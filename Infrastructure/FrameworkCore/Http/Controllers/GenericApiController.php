<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Infrastructure\FrameworkCore\Dispatchers\ActionDispatcher;
use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Infrastructure\FrameworkCore\Exceptions\ApiException;
use Infrastructure\FrameworkCore\Exceptions\NotFoundException;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Traits\ApiResponse;
use Infrastructure\FrameworkCore\Validation\EntityValidator;

class GenericApiController
{
    use ApiResponse;

    public function __construct(
        protected EntityRegistry $registry,
        protected DynamicRepository $repository,
        protected ActionDispatcher $dispatcher,
        protected EntityValidator $validator
    ) {}

    public function handle(Request $request, string $resource, ?string $id = null): JsonResponse
    {
        try {
            $config = $this->registry->getEntityConfig($resource);

            if (! $config) {
                return $this->notFound(
                    __('core::messages.resource_not_defined', ['resource' => $resource]),
                    ErrorCode::RESOURCE_NOT_DEFINED
                );
            }

            $method = $request->method();
            $includes = $request->query('include') ? explode(',', $request->query('include')) : [];
            $filters = $request->query();

            $routePath = $id ? "{$resource}/{$id}" : $resource;

            $actionResult = $this->dispatcher->dispatch($routePath, $method, $request);

            if ($actionResult === null && $id) {
                $actionResult = $this->dispatcher->dispatch($resource, $method, $request);
            }

            if ($actionResult !== null) {
                if ($method === 'POST') {
                    return $this->created($actionResult);
                }

                return $this->success($actionResult);
            }

            return match (true) {
                $method === 'GET' && ! $id => $this->handleList($resource, $config, $includes, $filters),
                $method === 'GET' && strlen($id) > 0 => $this->handleShow($resource, $id, $config, $includes),
                $method === 'POST' => $this->handleStore($resource, $config, $request, $includes),
                $method === 'PUT' => $this->handleUpdate($resource, $id, $config, $request, $includes),
                $method === 'PATCH' => $this->handleUpdate($resource, $id, $config, $request, $includes, true),
                $method === 'DELETE' => $this->handleDestroy($resource, $id),
                default => throw new ApiException(
                    __('core::messages.unsupported_method', ['method' => $method]),
                    ErrorCode::METHOD_NOT_ALLOWED,
                    405
                ),
            };

        } catch (ValidationException $e) {
            return $this->validationError($e->errors());

        } catch (ApiException $e) {
            return $this->error(
                $e->getMessage(),
                $e->getErrorCode(),
                $e->getStatusCode(),
                $e->getDetails()
            );

        } catch (\Throwable $e) {
            return $this->handleGenericError($e);
        }
    }

    protected function handleList(string $resource, array $config, array $includes, array $filters): JsonResponse
    {
        if (request()->has('cursor')) {
            $perPage = (int) request()->query('per_page', '15');
            $result = $this->repository->cursorPaginate($resource, $perPage, $includes, $filters);

            return $this->success(array_merge(['status' => 'success'], $result));
        }

        $perPage = (int) request()->query('per_page', '15');
        $paginator = $this->repository->paginate($resource, $perPage, $includes, $filters);

        return $this->success([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    protected function handleShow(string $resource, string $id, array $config, array $includes): JsonResponse
    {
        $data = $this->repository->findWithRelations($resource, $id, $includes);

        if ($data === null) {
            throw new NotFoundException(
                __('core::messages.resource_not_found', ['resource' => $resource])
            );
        }

        return $this->success($data);
    }

    protected function handleStore(string $resource, array $config, Request $request, array $includes): JsonResponse
    {
        $clean = $this->validator->validate($request->all(), $config, false);
        $data = $this->repository->insert($resource, $clean, $includes);

        return $this->created($data);
    }

    protected function handleUpdate(
        string $resource,
        string $id,
        array $config,
        Request $request,
        array $includes,
        bool $partial = false
    ): JsonResponse {
        $clean = $this->validator->validate($request->all(), $config, $partial);
        $data = $this->repository->update($resource, $id, $clean, $includes);

        return $this->success($data);
    }

    protected function handleDestroy(string $resource, string $id): JsonResponse
    {
        $this->repository->delete($resource, $id);

        return $this->deleted();
    }

    protected function handleGenericError(\Throwable $e): JsonResponse
    {
        $code = $e->getCode();

        if (! is_int($code) || $code < 400 || $code > 599) {
            $code = 500;
        }

        $debug = config('boundly.api.response_format.include_debug', config('app.debug', false));

        return $this->error(
            $e->getMessage(),
            ErrorCode::SERVER_ERROR,
            $code,
            $debug ? ['trace' => $e->getTrace()] : []
        );
    }
}
