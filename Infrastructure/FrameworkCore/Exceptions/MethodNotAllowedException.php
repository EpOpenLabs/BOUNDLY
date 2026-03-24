<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Exceptions;

use Infrastructure\FrameworkCore\Enums\ErrorCode;

class MethodNotAllowedException extends ApiException
{
    public function __construct(string $method, array $details = [])
    {
        parent::__construct(
            "Method {$method} is not allowed",
            ErrorCode::METHOD_NOT_ALLOWED,
            405,
            $details
        );
    }
}
