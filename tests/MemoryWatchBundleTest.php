<?php

declare(strict_types=1);

namespace Tourze\MemoryWatchBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\MemoryWatchBundle\MemoryWatchBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(MemoryWatchBundle::class)]
#[RunTestsInSeparateProcesses]
final class MemoryWatchBundleTest extends AbstractBundleTestCase
{
}
