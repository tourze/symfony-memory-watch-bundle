<?php

namespace Tourze\MemoryWatchBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\MemoryWatchBundle\DependencyInjection\MemoryWatchExtension;
use Tourze\MemoryWatchBundle\EventSubscriber\MemoryWatchSubscriber;

class MemoryWatchExtensionTest extends TestCase
{
    /**
     * 测试配置加载
     */
    public function testLoadConfiguration(): void
    {
        $container = new ContainerBuilder();
        $extension = new MemoryWatchExtension();
        $extension->load([], $container);

        // 检查服务是否已注册
        $this->assertTrue($container->has(MemoryWatchSubscriber::class));

        // 检查服务定义是否正确
        $definition = $container->getDefinition(MemoryWatchSubscriber::class);
        $this->assertTrue($definition->isAutowired());
        $this->assertTrue($definition->isAutoconfigured());
        $this->assertFalse($definition->isPublic());
    }
}
