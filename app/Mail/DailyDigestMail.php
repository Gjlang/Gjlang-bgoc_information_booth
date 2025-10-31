<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $kpis,
        public Collection $topRisks,
        public Collection $byAssignee,
        public Collection $ge,
        public bool $allClear = false,
    ) {}

    public function build()
    {
        $date = now('Asia/Kuala_Lumpur')->toDateString();
        $subject = $this->allClear
            ? "✅ [BGOC] Daily Digest — {$date} (All Clear)"
            : "📬 [BGOC] Daily Digest — {$date}";

        return $this->subject($subject)
            ->view('emails.daily_digest')
            ->with([
                'kpis'       => $this->kpis,
                'topRisks'   => $this->topRisks,
                'byAssignee' => $this->byAssignee,
                'ge'         => $this->ge,
                'allClear'   => $this->allClear,
            ]);
    }
}
