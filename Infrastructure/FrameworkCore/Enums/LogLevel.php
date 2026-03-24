<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Enums;

enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case NOTICE = 'notice';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';
    case ALERT = 'alert';
    case EMERGENCY = 'emergency';

    public static function fromPsr(int $level): self
    {
        return match (true) {
            $level >= 8 => self::DEBUG,
            $level >= 7 => self::INFO,
            $level >= 6 => self::NOTICE,
            $level >= 5 => self::WARNING,
            $level >= 4 => self::ERROR,
            $level >= 3 => self::CRITICAL,
            $level >= 2 => self::ALERT,
            default => self::EMERGENCY,
        };
    }

    public function toPsr(): int
    {
        return match ($this) {
            self::DEBUG => 8,
            self::INFO => 7,
            self::NOTICE => 6,
            self::WARNING => 5,
            self::ERROR => 4,
            self::CRITICAL => 3,
            self::ALERT => 2,
            self::EMERGENCY => 1,
        };
    }
}
