<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\SendSocialMessage;

class BotController extends Controller
{
    public function webHook(Request $request) {
        $updates = Telegram::getWebhookUpdates();
        $message = $updates->message;
        Log::info($message);
        if($updates && !empty($message) &&
            ((!empty($message['entities'][0]['type']) && ($message['entities'][0]['type']=='bot_command' )) ||
            (!empty($message['text']['entities'][0]['type']) && ($message['text']['entities'][0]['type']=='bot_command' ))
            )
        ) {
            Log::info('yes');
            if($message['chat']['type'] == 'private') {
                $textArray = explode(' ',$message['text']);
                if($textArray[0] == '/start') {
                    $users = User::where('telegram_username', $message['from']['username'])->get();
                    if(count($users)==0) {
                        // don't exist
                        return;
                    } else {
                        foreach($users as $user) {
                            $user->chat_id = $message->chat->id;
                            $user->save();
                        }

                        $users[0]->notify(new SendSocialMessage(
                            "Congrats {$users[0]->name} You have successfully linked your Telegram to Coordinape !\nI will remind you to allocate GIVEs whenever an epoch is ending"
                        ));
                    }
                }
            }
           // $updates->message->from->username =
        }

        return response()->json(['success' => 1]);
    }
}
