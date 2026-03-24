<?php

namespace Infrastructure\FrameworkCore\Dispatchers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Infrastructure\FrameworkCore\Attributes\Validation\Email;
use Infrastructure\FrameworkCore\Attributes\Validation\Min;
use Infrastructure\FrameworkCore\Attributes\Validation\Required;
use Infrastructure\FrameworkCore\Registry\ActionRegistry;
use ReflectionClass;

class ActionDispatcher
{
    public function __construct(protected ActionRegistry $registry) {}

    /**
     * Resuelve si existe una clase "Use Case / Action" del programador para sobrescribir
     * el comportamiento CRUD por defecto.
     * Retorna NULL si no hay una acción parcheada (causando fallback al CRUD auto).
     */
    public function dispatch(string $resource, string $method, Request $request)
    {
        $actionClass = $this->registry->getActionClass($resource, $method);

        if ($actionClass) {
            $action = app()->make($actionClass);

            // Reflexión para construir los parámetros automáticamente (DTOs)
            $methodReflection = new \ReflectionMethod($actionClass, 'execute');
            $args = [];

            foreach ($methodReflection->getParameters() as $param) {
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                    $className = $type->getName();
                    if ($className === Request::class) {
                        $args[] = $request;
                    } elseif (method_exists($className, 'fromArray')) {
                        // AQUÍ DESACOPLAMOS LARAVEL CREANDO EL DTO AUTOMÁTICAMENTE
                        $dto = $className::fromArray($request->all());

                        // VALIDACIÓN AUTOMÁTICA POR ATRIBUTOS
                        $this->validateDto($dto);

                        $args[] = $dto;
                    } else {
                        $args[] = app()->make($className);
                    }
                } else {
                    $args[] = null;
                }
            }

            return $action->execute(...$args);
        }

        return null;
    }

    protected function validateDto(object $dto): void
    {
        $reflection = new ReflectionClass($dto);
        $rules = [];
        $data = (array) $dto;

        foreach ($reflection->getProperties() as $property) {
            $propName = $property->getName();
            $propRules = [];

            if (! empty($property->getAttributes(Required::class))) {
                $propRules[] = 'required';
            }
            if (! empty($property->getAttributes(Email::class))) {
                $propRules[] = 'email';
            }
            $minAttr = $property->getAttributes(Min::class);
            if (! empty($minAttr)) {
                $propRules[] = 'min:'.$minAttr[0]->newInstance()->value;
            }

            if (! empty($propRules)) {
                $rules[$propName] = $propRules;
            }
        }

        if (! empty($rules)) {
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                throw new \Exception('Validación Fallida: '.implode(', ', $validator->errors()->all()), 422);
            }
        }
    }
}
