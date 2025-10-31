<?php

namespace Tourze\MemoryWatchBundle\Tests;

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * 测试用的 Logger 实现
 */
class TestLogger implements LoggerInterface
{
    /** @var array<array{message: \Stringable|string, context: array<mixed>}> */
    public array $warnings = [];

    /** @var array<array{message: \Stringable|string, context: array<mixed>}> */
    public array $errors = [];

    /** @var array<array{message: \Stringable|string, context: array<mixed>}> */
    public array $infos = [];

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->errors[] = ['message' => $message, 'context' => $context];
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->errors[] = ['message' => $message, 'context' => $context];
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->errors[] = ['message' => $message, 'context' => $context];
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->errors[] = ['message' => $message, 'context' => $context];
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->warnings[] = ['message' => $message, 'context' => $context];
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->infos[] = ['message' => $message, 'context' => $context];
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->infos[] = ['message' => $message, 'context' => $context];
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->infos[] = ['message' => $message, 'context' => $context];
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if ('warning' === $level) {
            $this->warning($message, $context);
        } elseif ('error' === $level) {
            $this->error($message, $context);
        } else {
            $this->info($message, $context);
        }
    }
}
