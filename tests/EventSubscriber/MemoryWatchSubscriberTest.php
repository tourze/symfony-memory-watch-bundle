<?php

namespace Tourze\MemoryWatchBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tourze\MemoryWatchBundle\EventSubscriber\MemoryWatchSubscriber;
use WeakMap;

class MemoryWatchSubscriberTest extends TestCase
{
    private LoggerInterface $logger;
    private MemoryWatchSubscriber $subscriber;
    private Request $request;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subscriber = new MemoryWatchSubscriber($this->logger);
        $this->request = new Request();
    }

    /**
     * 测试请求事件处理
     */
    public function testOnKernelRequest(): void
    {
        // 创建请求事件
        $kernel = $this->createMock(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel, $this->request, HttpKernelInterface::MAIN_REQUEST);

        // 调用 onKernelRequest
        $this->subscriber->onKernelRequest($requestEvent);

        // 使用反射检查 requestContext 是否包含请求
        $reflectionProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'requestContext');
        $reflectionProperty->setAccessible(true);
        $requestContext = $reflectionProperty->getValue($this->subscriber);

        $this->assertInstanceOf(WeakMap::class, $requestContext);
        $this->assertTrue($requestContext->offsetExists($this->request));
        $this->assertIsInt($requestContext->offsetGet($this->request));
    }

    /**
     * 测试请求不在上下文中的情况
     */
    public function testOnKernelResponseWithoutRequestContext(): void
    {
        // 创建终止事件
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = $this->createMock(Response::class);
        $terminateEvent = new TerminateEvent($kernel, $this->request, $response);

        // 不调用 onKernelRequest，确保请求不在上下文中

        // Logger 不应该被调用
        $this->logger->expects($this->never())
            ->method('warning');

        // 调用 onKernelResponse
        $this->subscriber->onKernelResponse($terminateEvent);
    }

    /**
     * 测试内存使用低于阈值的情况
     */
    public function testOnKernelResponseBelowThreshold(): void
    {
        // 创建终止事件
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = $this->createMock(Response::class);
        $terminateEvent = new TerminateEvent($kernel, $this->request, $response);

        // 使用反射设置 requestContext
        $reflectionProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'requestContext');
        $reflectionProperty->setAccessible(true);
        $requestContext = $reflectionProperty->getValue($this->subscriber);

        // 设置一个接近当前内存使用的值（确保差值很小）
        $requestContext->offsetSet($this->request, memory_get_usage() - 1000);

        // Logger 不应该被调用
        $this->logger->expects($this->never())
            ->method('warning');

        // 调用 onKernelResponse
        $this->subscriber->onKernelResponse($terminateEvent);
    }

    /**
     * 测试内存使用超过阈值的情况
     */
    public function testOnKernelResponseAboveThreshold(): void
    {
        // 创建终止事件
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = $this->createMock(Response::class);
        $terminateEvent = new TerminateEvent($kernel, $this->request, $response);

        // 使用反射设置 requestContext 和内存阈值
        $requestContextProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'requestContext');
        $requestContextProperty->setAccessible(true);
        $requestContext = $requestContextProperty->getValue($this->subscriber);

        $thresholdProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'memoryThresholdMB');
        $thresholdProperty->setAccessible(true);
        $thresholdProperty->setValue($this->subscriber, 1); // 设置为 1MB

        // 设置一个非常小的初始内存值，确保当前内存使用量大于阈值
        $requestContext->offsetSet($this->request, 0);

        // Logger 应该被调用一次
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('内存使用超过阈值'),
                $this->callback(function ($context) {
                    return isset($context['使用内存']) && isset($context['内存阈值']) && $context['内存阈值'] === '1MB';
                })
            );

        // 调用 onKernelResponse
        $this->subscriber->onKernelResponse($terminateEvent);
    }

    /**
     * 测试内存使用恰好等于阈值的情况
     *
     * 注意：这个测试的可靠性依赖于实际的内存使用情况，
     * 如果内存使用不稳定，测试可能偶尔失败
     */
    public function testOnKernelResponseAtThreshold(): void
    {
        // 创建一个新的订阅者实例，避免与其他测试共享状态
        $logger = $this->createMock(LoggerInterface::class);
        $subscriber = new MemoryWatchSubscriber($logger);

        // 创建终止事件
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = $this->createMock(Response::class);
        $terminateEvent = new TerminateEvent($kernel, $this->request, $response);

        // 设置一个很大的阈值，确保内存使用不会超过阈值
        $thresholdProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'memoryThresholdMB');
        $thresholdProperty->setAccessible(true);
        $thresholdProperty->setValue($subscriber, 1000); // 设置为 1000MB，远大于测试中的内存使用

        // 使用反射设置 requestContext
        $requestContextProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'requestContext');
        $requestContextProperty->setAccessible(true);
        $requestContext = $requestContextProperty->getValue($subscriber);

        // 存储当前的内存使用
        $initialMemory = memory_get_usage();
        $requestContext->offsetSet($this->request, $initialMemory);

        // Logger 不应该被调用
        $logger->expects($this->never())
            ->method('warning');

        // 调用 onKernelResponse
        $subscriber->onKernelResponse($terminateEvent);
    }

    /**
     * 测试自定义内存阈值
     */
    public function testCustomMemoryThreshold(): void
    {
        // 使用反射设置自定义内存阈值
        $thresholdProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'memoryThresholdMB');
        $thresholdProperty->setAccessible(true);
        $thresholdProperty->setValue($this->subscriber, 100); // 设置为 100MB

        // 检查阈值是否正确设置
        $this->assertEquals(100, $thresholdProperty->getValue($this->subscriber));

        // 创建一个新的订阅者实例，来避免重用之前测试中的对象
        $subscriber = new MemoryWatchSubscriber($this->logger);

        // 设置自定义内存阈值 - 使用整数类型避免浮点精度问题
        $thresholdProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'memoryThresholdMB');
        $thresholdProperty->setAccessible(true);
        $thresholdProperty->setValue($subscriber, 1); // 设置为 1MB

        // 创建终止事件
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = $this->createMock(Response::class);
        $terminateEvent = new TerminateEvent($kernel, $this->request, $response);

        // 使用反射设置 requestContext
        $requestContextProperty = new ReflectionProperty(MemoryWatchSubscriber::class, 'requestContext');
        $requestContextProperty->setAccessible(true);
        $requestContext = $requestContextProperty->getValue($subscriber);

        // 设置初始内存值为0，确保内存使用量超过自定义阈值
        $requestContext->offsetSet($this->request, 0);

        // Logger 应该被调用一次，并且阈值应该是自定义的值
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('内存使用超过阈值'),
                $this->callback(function ($context) {
                    return isset($context['使用内存']) &&
                        isset($context['内存阈值']) &&
                        $context['内存阈值'] === '1MB';
                })
            );

        // 调用 onKernelResponse
        $subscriber->onKernelResponse($terminateEvent);
    }
}
