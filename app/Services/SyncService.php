<?php

namespace App\Services;

use App\Models\SyncEvent;
use Illuminate\Support\Facades\Http;

class SyncService
{
    public function record(string $entity, ?int $entityId, string $action, array $payload): SyncEvent
    {
        return SyncEvent::create([
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'payload' => $payload,
        ]);
    }

    public function run(): int
    {
        if (!$this->remoteUrl()) {
            return 0;
        }

        $synced = 0;

        $events = SyncEvent::unsynced()->orderBy('id')->limit(50)->get();

        foreach ($events as $event) {
            try {
                $response = Http::timeout(10)
                    ->withToken(config('services.sync.token'))
                    ->post($this->remoteUrl(), $event->only(['entity', 'entity_id', 'action', 'payload']));

                if ($response->successful()) {
                    $event->update([
                        'synced' => true,
                        'synced_at' => now(),
                        'last_error' => null,
                    ]);
                    $synced++;

                    continue;
                }

                $this->fail($event, 'HTTP ' . $response->status());
            } catch (\Throwable $e) {
                $this->fail($event, $e->getMessage());
            }

            if ($event->refresh()->attempts >= 5) {
                break;
            }
        }

        return $synced;
    }

    private function fail(SyncEvent $event, string $error): void
    {
        $event->increment('attempts');
        $event->update(['last_error' => $error]);
    }

    private function remoteUrl(): ?string
    {
        $url = config('services.sync.url');

        return $url ? trim($url) : null;
    }
}