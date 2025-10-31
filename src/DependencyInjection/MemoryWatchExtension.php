<?php

namespace Tourze\MemoryWatchBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class MemoryWatchExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
