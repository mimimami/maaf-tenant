<?php

declare(strict_types=1);

namespace MAAF\Tenant\Queue;

use MAAF\Core\EventBus\EventMessage;
use MAAF\Core\EventBus\QueueAdapterInterface;
use MAAF\Tenant\TenantManager;
use FastRoute\Dispatcher;

/**
 * Tenant Queue
 * 
 * Tenant-aware queue izoláció.
 * 
 * @version 1.0.0
 */
final class TenantQueue implements QueueAdapterInterface
{
    public function __construct(
        private readonly QueueAdapterInterface $baseAdapter,
        private readonly TenantManager $tenantManager
    ) {
    }

    /**
     * Get tenant-specific queue name
     * 
     * @param string $queue Base queue name
     * @return string
     */
    private function getTenantQueueName(string $queue): string
    {
        $tenantId = $this->tenantManager->getTenantId() ?? 'default';
        return "tenant:{$tenantId}:{$queue}";
    }

    public function publish(string $queue, EventMessage $message, array $options = []): ?string
    {
        $tenantQueue = $this->getTenantQueueName($queue);
        
        // Add tenant ID to message metadata if not present
        $tenantId = $this->tenantManager->getTenantId();
        if ($tenantId !== null) {
            $message = new EventMessage(
                id: $message->id,
                eventName: $message->eventName,
                payload: $message->payload,
                moduleName: $message->moduleName,
                metadata: array_merge($message->metadata, [
                    'tenant_id' => $tenantId,
                ]),
                retryCount: $message->retryCount,
                timestamp: $message->timestamp
            );
        }

        return $this->baseAdapter->publish($tenantQueue, $message, $options);
    }

    public function consume(string $queue, callable $handler, array $options = []): void
    {
        $tenantQueue = $this->getTenantQueueName($queue);
        $this->baseAdapter->consume($tenantQueue, $handler, $options);
    }

    public function acknowledge(string $messageId): void
    {
        $this->baseAdapter->acknowledge($messageId);
    }

    public function reject(string $messageId, bool $requeue = true): void
    {
        $this->baseAdapter->reject($messageId, $requeue);
    }

    public function declareQueue(string $queue, array $options = []): void
    {
        $tenantQueue = $this->getTenantQueueName($queue);
        $this->baseAdapter->declareQueue($tenantQueue, $options);
    }

    public function declareExchange(string $exchange, string $type = 'topic', array $options = []): void
    {
        // Exchange names don't need tenant prefix, but routing keys do
        $this->baseAdapter->declareExchange($exchange, $type, $options);
    }

    public function bindQueue(string $queue, string $exchange, string $routingKey = ''): void
    {
        $tenantQueue = $this->getTenantQueueName($queue);
        $tenantRoutingKey = $this->getTenantRoutingKey($routingKey);
        $this->baseAdapter->bindQueue($tenantQueue, $exchange, $tenantRoutingKey);
    }

    /**
     * Get tenant-specific routing key
     * 
     * @param string $routingKey Base routing key
     * @return string
     */
    private function getTenantRoutingKey(string $routingKey): string
    {
        $tenantId = $this->tenantManager->getTenantId() ?? 'default';
        
        if (empty($routingKey)) {
            return "tenant.{$tenantId}";
        }

        return "tenant.{$tenantId}.{$routingKey}";
    }
}
