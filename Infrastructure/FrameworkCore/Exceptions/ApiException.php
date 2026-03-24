<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Exceptions;

use Exception;
use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Throwable;

class ApiException extends Exception
{
    protected ErrorCode $errorCode;

    protected array $details;

    public function __construct(
        string $message,
        ErrorCode|string $errorCode = ErrorCode::INTERNAL_ERROR,
        int $statusCode = 500,
        array $details = [],
        ?Throwable $previous = null
    ) {
        $this->errorCode = $errorCode instanceof ErrorCode ? $errorCode : ErrorCode::from($errorCode);
        $this->details = $details;

        parent::__construct($message, $statusCode, $previous);
    }

    public function getErrorCode(): ErrorCode
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getStatusCode(): int
    {
        return parent::getCode();
    }

    public function toArray(): array
    {
        return [
            'code' => $this->errorCode->value,
            'message' => $this->getMessage(),
            'details' => $this->details,
        ];
    }
}
