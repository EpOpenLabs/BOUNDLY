<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Logging;

use Illuminate\Support\Facades\Log;

class AuditLogger
{
    protected string $channel;
    protected bool $enabled;
    protected array $auditableEvents;

    public function __construct()
    {
        $config = config('boundly.logging.audit', []);
        $this->enabled = $config['enabled'] ?? true;
        $this->channel = $config['channel'] ?? 'single';
        $this->auditableEvents = $config['events'] ?? ['created', 'updated', 'deleted'];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function logCreated(
        string $entity,
        string|int $entityId,
        ?string $userId,
        array $data = []
    ): void {
        if (! $this->shouldAudit('created')) {
            return;
        }

        $this->log('created', $entity, $entityId, $userId, $data);
    }

    public function logUpdated(
        string $entity,
        string|int $entityId,
        ?string $userId,
        array $oldData = [],
        array $newData = []
    ): void {
        if (! $this->shouldAudit('updated')) {
            return;
        }

        $changes = $this->calculateChanges($oldData, $newData);

        $this->log('updated', $entity, $entityId, $userId, [
            'old' => $oldData,
            'new' => $newData,
            'changes' => $changes,
        ]);
    }

    public function logDeleted(
        string $entity,
        string|int $entityId,
        ?string $userId,
        array $data = []
    ): void {
        if (! $this->shouldAudit('deleted')) {
            return;
        }

        $this->log('deleted', $entity, $entityId, $userId, ['deleted_data' => $data]);
    }

    public function logAccessed(
        string $entity,
        string|int $entityId,
        ?string $userId,
        string $action = 'read'
    ): void {
        if (! $this->shouldAudit('accessed')) {
            return;
        }

        $this->log('accessed', $entity, $entityId, $userId, ['action' => $action]);
    }

    protected function shouldAudit(string $event): bool
    {
        return $this->enabled && in_array($event, $this->auditableEvents, true);
    }

    protected function log(
        string $event,
        string $entity,
        string|int $entityId,
        ?string $userId,
        array $data = []
    ): void {
        $payload = [
            'audit' => true,
            'app' => config('app.name', 'BOUNDLY'),
            'timestamp' => now()->toIso8601String(),
            'event' => $event,
            'entity' => [
                'type' => $entity,
                'id' => $entityId,
            ],
            'user' => [
                'id' => $userId,
            ],
            'data' => $data,
        ];

        Log::channel($this->channel)->info("[AUDIT] {$event} {$entity}:{$entityId}", $payload);
    }

    protected function calculateChanges(array $old, array $new): array
    {
        $changes = [];

        foreach ($new as $key => $newValue) {
            if (! array_key_exists($key, $old)) {
                $changes[$key] = ['added' => $newValue];
            } elseif ($old[$key] !== $newValue) {
                $changes[$key] = [
                    'from' => $old[$key],
                    'to' => $newValue,
                ];
            }
        }

        foreach ($old as $key => $oldValue) {
            if (! array_key_exists($key, $new)) {
                $changes[$key] = ['removed' => $oldValue];
            }
        }

        return $changes;
    }
}
