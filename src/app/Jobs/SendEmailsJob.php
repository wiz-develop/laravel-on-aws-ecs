<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ExampleMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 送信先のメールアドレスを設定
        $to = 'test@example.com';

        // バックグラウンドでメール送信
        Mail::to($to)->send(new ExampleMail(
            envelopeSubject: 'テストメール (Background Job から送信)',
            envelopeFromName: 'From Background Job',
            body: 'これは Background Job から送信されたテストメールです。',
        ));
    }
}
