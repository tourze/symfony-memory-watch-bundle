<?php

namespace Tourze\MemoryWatchBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WeakMap;

class MemoryWatchSubscriber
{
    private int $memoryThresholdMB = 50;

    /**
     * @var WeakMap<object, int>
     */
    private WeakMap $requestContext;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        $this->requestContext = new WeakMap();
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 999999)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $this->requestContext->offsetSet($request, memory_get_usage());
    }

    #[AsEventListener(event: KernelEvents::TERMINATE , priority: -999999)]
    public function onKernelResponse(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->requestContext->offsetExists($request)) {
            return;
        }

        $startMemoryUsage = $this->requestContext->offsetGet($request) ?? 0;
        $endMemoryUsage = memory_get_usage();
        $memoryUsed = $endMemoryUsage - $startMemoryUsage;

        $memoryThresholdBytes = $this->memoryThresholdMB * 1024 * 1024;

        if ($memoryUsed > $memoryThresholdBytes) {
            $this->logger->warning('内存使用超过阈值', [
                '使用内存' => $memoryUsed / 1024 / 1024,
                '内存阈值' => $this->memoryThresholdMB . 'MB',
            ]);
        }
    }
}
