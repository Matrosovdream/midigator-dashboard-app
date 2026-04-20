<?php

namespace App\Jobs\Emails;

use App\Repositories\Email\EmailLogRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $emailLogId) {}

    public function handle(EmailLogRepo $logRepo): void
    {
        $record = $logRepo->getByID($this->emailLogId);
        if (!$record) {
            return;
        }

        $log = $record['Model'];

        Mail::html($log->body, function ($message) use ($log) {
            $message->to($log->to_email)->subject($log->subject);
        });

        $logRepo->markStatus($log->id, 'sent', now());
    }

    public function failed(\Throwable $e): void
    {
        app(EmailLogRepo::class)->markStatus($this->emailLogId, 'failed');
    }
}
