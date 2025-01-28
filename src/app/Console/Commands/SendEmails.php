<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\ExampleMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a mail for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 送信先のメールアドレスを設定
        $to = 'test@example.com';

        // メール送信
        Mail::to($to)->send(new ExampleMail(
            envelopeSubject: 'テストメール (Command から送信)',
            envelopeFromName: 'From Command',
            body: 'これは Command から送信されたテストメールです。',
        ));

        $this->info('Test email sent successfully to ' . $to);
    }
}
