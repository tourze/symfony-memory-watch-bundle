<?php

namespace Tourze\MemoryWatchBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\MemoryWatchBundle\MemoryWatchBundle;

class MemoryWatchBundleTest extends TestCase
{
    /**
     * 测试 Bundle 实例化
     */
    public function testBundleInstantiation(): void
    {
        $bundle = new MemoryWatchBundle();
        $this->assertInstanceOf(MemoryWatchBundle::class, $bundle);
    }
}
