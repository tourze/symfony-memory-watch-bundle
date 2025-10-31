<?php

namespace Tourze\MemoryWatchBundle;

use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class MemoryWatchBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            MonologBundle::class => ['all' => true],
        ];
    }
}
