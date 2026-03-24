<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Exceptions;

use Infrastructure\FrameworkCore\Enums\ErrorCode;

class ValidationException extends ApiException
{
    protected array $errors;

    public function __construct(string|array $message = 'Validation failed', array $errors = [])
    {
        $messageText = is_array($message) ? 'Validation failed' : $message;
        $this->errors = is_array($message) ? $message : $errors;

        parent::__construct(
            $messageText,
            ErrorCode::VALIDATION_FAILED,
            422,
            $this->errors
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
