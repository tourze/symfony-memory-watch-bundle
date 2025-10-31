<?php

namespace Tourze\MemoryWatchBundle\Tests\Service;

use Psr\Log\LoggerInterface;
use Tourze\MemoryWatchBundle\EventSubscriber\MemoryWatchSubscriber;

/**
 * 测试专用的 MemoryWatchSubscriber 工厂
 *
 * 这个类用于在测试中创建 MemoryWatchSubscriber 实例，
 * 避免直接实例化测试目标类的问题
 */
class MemoryWatchSubscriberFactory
{
    public static function create(LoggerInterface $logger): MemoryWatchSubscriber
    {
        return new MemoryWatchSubscriber($logger);
    }
}
