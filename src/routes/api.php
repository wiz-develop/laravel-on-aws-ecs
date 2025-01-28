<?php

declare(strict_types=1);

use App\Jobs\SendEmailsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', static function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
 * TODO: 実案件では削除してください。
 * バックグラウンドメール送信テスト用エンドポイント
 */
Route::get('send-emails', static function (Request $request) {
    SendEmailsJob::dispatch()->onQueue('example-emails.fifo')->delay(now());

    return response()->json(
        status:200,
        headers:['Content-Type' => 'application/json'],
        data:['message' => '✨ Emails are being sent. 📨'],
        options: JSON_UNESCAPED_UNICODE
    );
});
