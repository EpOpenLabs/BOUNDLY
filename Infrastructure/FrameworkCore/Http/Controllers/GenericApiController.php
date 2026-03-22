<?php

namespace Infrastructure\FrameworkCore\Http\Controllers;

use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Infrastructure\FrameworkCore\Dispatchers\ActionDispatcher;

class GenericApiController
{
    public function __construct(
        protected EntityRegistry $registry,
        protected DynamicRepository $repository,
        protected ActionDispatcher $dispatcher
    ) {}

    public function handle(Request $request, string $resource, ?string $id = null)
    {
        try {
            // 1. Validate if entity is registered in Domain
            if (!$this->registry->getEntityConfig($resource)) {
                return response()->json([
                    'status' => 'error',
                    'message' => __("core::messages.resource_not_defined", ['resource' => $resource])
                ], 404);
            }

            $method = $request->method();
            $includes = $request->query('include') ? explode(',', $request->query('include')) : [];
            $filters = $request->query();

            // 2. Extensibility Layer: Override with Custom Action if defined
            $actionResult = $this->dispatcher->dispatch($resource, $method, $request);
            if ($actionResult !== null) {
                $status = ($method === 'POST') ? 201 : 200;
                return response()->json([
                    'status' => 'success',
                    'data' => $actionResult
                ], $status);
            }

            // 3. Secure Fallback: Automatic CRUD via Dynamic Repository
            if ($method === 'GET' && !$id) {
                // LOAD WITH NATIVE PAGINATION + DYNAMIC FILTERS
                $perPage = (int) $request->query('per_page', 15);
                $paginator = $this->repository->paginate($resource, $perPage, $includes, $filters);

                return response()->json([
                    'status' => 'success',
                    'data' => $paginator->items(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total()
                    ]
                ]);
            }

            $response = match ($method) {
                'GET' => [
                    'status' => 'success',
                    'data' => $this->repository->find($resource, $id, $filters)
                ],

                'POST' => [
                    'status' => 'success',
                    'message' => __('core::messages.resource_created_magic'),
                    'data' => $this->repository->insert($resource, $request->all())
                ],

                'PUT', 'PATCH' => [
                    'status' => 'success',
                    'message' => __('core::messages.resource_updated_magic'),
                    'data' => $this->repository->update($resource, $id, $request->all())
                ],

                'DELETE' => [
                    'status' => 'success',
                    'message' => __('core::messages.resource_deleted_magic'),
                    'data' => $this->repository->delete($resource, $id)
                ],

                default => throw new \Exception(__("core::messages.unsupported_method", ['method' => $method]), 405)
            };

            $statusCode = ($method === 'POST') ? 201 : 200;
            return response()->json($response, $statusCode);

        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code < 400 || $code > 599) $code = 500;
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], $code);
        }
    }
}
