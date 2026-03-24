<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Exceptions;

use Infrastructure\FrameworkCore\Enums\ErrorCode;

class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Unauthorized', array $details = [])
    {
        parent::__construct(
            $message,
            ErrorCode::UNAUTHORIZED,
            401,
            $details
        );
    }
}
