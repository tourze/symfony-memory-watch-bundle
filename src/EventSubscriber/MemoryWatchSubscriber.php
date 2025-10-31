<?php

declare(strict_types=1);

namespace Tourze\MemoryWatchBundle\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[WithMonologChannel(channel: 'memory_watch')]
class MemoryWatchSubscriber
{
    private int $memoryThresholdMB = 50;

    /**
     * @var \WeakMap<object, array{memory: int, time: float}>
     */
    private \WeakMap $requestContext;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        $this->requestContext = new \WeakMap();
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 999999)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $this->requestContext->offsetSet($request, [
            'memory' => memory_get_usage(),
            'time' => microtime(true),
        ]);
    }

    #[AsEventListener(event: KernelEvents::TERMINATE, priority: -999999)]
    public function onKernelResponse(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->requestContext->offsetExists($request)) {
            return;
        }

        $context = $this->requestContext->offsetGet($request);
        $startMemoryUsage = $context['memory'] ?? 0;
        $startTime = $context['time'] ?? microtime(true);

        $endMemoryUsage = memory_get_usage();
        $endTime = microtime(true);

        $memoryUsed = $endMemoryUsage - $startMemoryUsage;
        $executionTime = $endTime - $startTime;

        // 内存使用警示
        $memoryThresholdBytes = $this->memoryThresholdMB * 1024 * 1024;

        if ($memoryUsed > $memoryThresholdBytes) {
            $this->logger->warning('内存使用超过阈值', [
                '使用内存' => round($memoryUsed / 1024 / 1024, 2) . 'MB',
                '内存阈值' => $this->memoryThresholdMB . 'MB',
                '执行时间' => round($executionTime, 3) . '秒',
                '请求URI' => $request->getRequestUri(),
                '峰值内存' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
            ]);
        }
    }
}
