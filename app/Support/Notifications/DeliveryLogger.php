<?php

namespace App\Support\Notifications;

use App\Models\DeliveryLog;

class DeliveryLogger
{
    public function shouldDebounce(int $userId, int $itemId, string $channel, string $event): bool
    {
        $since = now()->subDay();
        return DeliveryLog::query()
            ->where('user_id', $userId)
            ->where('item_id', $itemId)
            ->where('channel', $channel)
            ->where('event', $event)
            ->where('sent_at', '>=', $since)
            ->exists();
    }

    public function logSent(int $userId, int $itemId, string $channel, string $event, array $meta = []): void
    {
        DeliveryLog::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'channel' => $channel,
            'event'   => $event,
            'status'  => 'sent',
            'meta'    => $meta ?: null,
            'sent_at' => now(),
        ]);
    }

    public function logSkipped(int $userId, int $itemId, string $channel, string $event, array $meta = []): void
    {
        DeliveryLog::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'channel' => $channel,
            'event'   => $event,
            'status'  => 'skipped',
            'meta'    => $meta ?: null,
            'sent_at' => now(),
        ]);
    }
}
