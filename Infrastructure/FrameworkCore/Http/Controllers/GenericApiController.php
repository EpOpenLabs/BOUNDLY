<?php

namespace Infrastructure\FrameworkCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Infrastructure\FrameworkCore\Dispatchers\ActionDispatcher;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Validation\EntityValidator;

class GenericApiController
{
    public function __construct(
        protected EntityRegistry $registry,
        protected DynamicRepository $repository,
        protected ActionDispatcher $dispatcher,
        protected EntityValidator $validator
    ) {}

    public function handle(Request $request, string $resource, ?string $id = null)
    {
        try {
            // 1. Validate the resource is a registered Domain entity
            $config = $this->registry->getEntityConfig($resource);
            if (! $config) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('core::messages.resource_not_defined', ['resource' => $resource]),
                ], 404);
            }

            $method = $request->method();
            $includes = $request->query('include') ? explode(',', $request->query('include')) : [];
            $filters = $request->query();

            // 2. Extensibility Layer: hand off to a custom Application Action if defined
            $routePath = $id ? "{$resource}/{$id}" : $resource;

            // Try explicit composite route first (e.g. 'posts/test-broadcast')
            $actionResult = $this->dispatcher->dispatch($routePath, $method, $request);

            // If no composite action, try base resource action (e.g. 'posts')
            if ($actionResult === null && $id) {
                $actionResult = $this->dispatcher->dispatch($resource, $method, $request);
            }

            if ($actionResult !== null) {
                $status = ($method === 'POST') ? 201 : 200;

                return response()->json(['status' => 'success', 'data' => $actionResult], $status);
            }

            // 3. Automatic CRUD with Validation + Sanitization
            $response = match (true) {

                // GET /resource (list, with cursor or offset pagination)
                $method === 'GET' && ! $id => $this->handleList($resource, $config, $includes, $filters),

                // GET /resource/{id}
                $method === 'GET' && strlen($id) > 0 => [
                    'status' => 'success',
                    'data' => $this->repository->findWithRelations($resource, $id, $includes)
                                    ?? throw new \Exception(__('core::messages.resource_not_found', ['resource' => $resource]), 404),
                ],

                // POST /resource
                $method === 'POST' => (function () use ($resource, $config, $request, $includes) {
                    $clean = $this->validator->validate($request->all(), $config, false);

                    return [
                        'status' => 'success',
                        'message' => __('core::messages.resource_created_magic'),
                        'data' => $this->repository->insert($resource, $clean, $includes),
                    ];
                })(),

                // PUT /resource/{id}
                $method === 'PUT' => (function () use ($resource, $config, $request, $id, $includes) {
                    $clean = $this->validator->validate($request->all(), $config, false);

                    return [
                        'status' => 'success',
                        'message' => __('core::messages.resource_updated_magic'),
                        'data' => $this->repository->update($resource, $id, $clean, $includes),
                    ];
                })(),

                // PATCH /resource/{id} — partial update
                $method === 'PATCH' => (function () use ($resource, $config, $request, $id, $includes) {
                    $clean = $this->validator->validate($request->all(), $config, true);

                    return [
                        'status' => 'success',
                        'message' => __('core::messages.resource_updated_magic'),
                        'data' => $this->repository->update($resource, $id, $clean, $includes),
                    ];
                })(),

                // DELETE /resource/{id}
                $method === 'DELETE' => [
                    'status' => 'success',
                    'message' => __('core::messages.resource_deleted_magic'),
                    'data' => $this->repository->delete($resource, $id),
                ],

                default => throw new \Exception(
                    __('core::messages.unsupported_method', ['method' => $method]), 405
                ),
            };

            $statusCode = ($method === 'POST') ? 201 : 200;

            return response()->json($response, $statusCode);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code < 400 || $code > 599) {
                $code = 500;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : [],
            ], $code);
        }
    }

    /**
     * Handles GET list requests.
     * Automatically switches to cursor pagination when ?cursor= is present.
     */
    protected function handleList(string $resource, array $config, array $includes, array $filters): array
    {
        // Cursor-based pagination (efficient for large tables)
        if (request()->has('cursor')) {
            $perPage = (int) request()->query('per_page', '15');
            $result = $this->repository->cursorPaginate($resource, $perPage, $includes, $filters);

            return array_merge(['status' => 'success'], $result);
        }

        // Standard offset pagination
        $perPage = (int) request()->query('per_page', '15');
        $paginator = $this->repository->paginate($resource, $perPage, $includes, $filters);

        return [
            'status' => 'success',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
