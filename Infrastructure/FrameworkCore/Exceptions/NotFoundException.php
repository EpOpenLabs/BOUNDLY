<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Exceptions;

use Infrastructure\FrameworkCore\Enums\ErrorCode;

class NotFoundException extends ApiException
{
    public function __construct(string $message = 'Resource not found', array $details = [])
    {
        parent::__construct(
            $message,
            ErrorCode::RESOURCE_NOT_FOUND,
            404,
            $details
        );
    }
}
