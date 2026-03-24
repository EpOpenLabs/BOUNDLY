<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Exceptions;

use Infrastructure\FrameworkCore\Enums\ErrorCode;

class ForbiddenException extends ApiException
{
    public function __construct(string $message = 'Forbidden', array $details = [])
    {
        parent::__construct(
            $message,
            ErrorCode::FORBIDDEN,
            403,
            $details
        );
    }
}
