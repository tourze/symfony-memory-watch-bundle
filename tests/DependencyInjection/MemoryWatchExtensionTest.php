<?php

namespace Tourze\MemoryWatchBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\MemoryWatchBundle\DependencyInjection\MemoryWatchExtension;
use Tourze\MemoryWatchBundle\EventSubscriber\MemoryWatchSubscriber;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(MemoryWatchExtension::class)]
final class MemoryWatchExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    /**
     * 测试配置加载
     */
    public function testLoadConfiguration(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
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
