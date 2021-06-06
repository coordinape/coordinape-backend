<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    public function webHook(Request $request) {
        $updates = Telegram::getWebhookUpdates();
        Log::info($updates);
    }
}
