<?php

class SendItemNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $event,
        public int $itemId,
        public array $context = []
    ) {}

    public function handle(RecipientResolver $resolver, DeliveryLogger $logger)
    {
        $item = Item::find($this->itemId);
        if (!$item) return;

        $recipients = match ($this->event) {
            ItemEvent::CREATED,
            ItemEvent::STATUS_CHANGED,
            ItemEvent::ASSIGNEE_CHANGED => $resolver->resolveForItem($item, $this->event, $this->context),
            default => [],
        };

        foreach ($recipients as $user) {
            if ($logger->shouldDebounce($user->id, $item->id, 'mail', $this->event)) {
                $logger->logSkipped($user->id, $item->id, 'mail', $this->event, ['reason'=>'debounced']);
                continue;
            }

            // Send email
            Mail::to($user->email)->send(new ItemEventMail($this->event, $item, $this->context));

            $logger->logSent($user->id, $item->id, 'mail', $this->event);
        }

        // (Later) WhatsApp send here
    }
}
