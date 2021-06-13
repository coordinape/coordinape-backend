<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\SendSocialMessage;
use App\Models\Circle;

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
            if($message['chat']['type'] == 'private') {
                $this->processPrivateCommands($message);
            }
            else if($message['chat']['type'] == 'group') {
                $this->processGroupCommands($message);
            }
        }

       // return response()->json(['success' => 1]);
    }

    private function processPrivateCommands($message) {
        $textArray = explode(' ',$message['text']);
        $command = $textArray[0];
        switch($command) {
            case '/start':
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
                        "Congrats {$users[0]->name} You have successfully registered your Telegram to Coordinape !\nI will occasionally send you important reminders!"
                    ));
                }
                break;
            case '/give':
                break;

            case '/announce':
                $this->sendAnnouncement($message);
                break;
        }
    }

    private function processGroupCommands($message) {
        $textArray = explode(' ',$message['text']);
        $command = $textArray[0];

        switch($command) {
            case '/give':
                break;

            case '/announce':
               $this->sendAnnouncement($message);
             break;
        }
    }

    private function sendAnnouncement($message) {
        $annText = substr($message['text'],10);
        $user = User::where('telegram_username', $message['from']['username'])
            ->where(function($q) {
                $q->where('ann_power', 1)->orWhere('super',1);
            })->first();
        if($user) {
            $circles = Circle::whereNotNull('telegram_id')->get();
            foreach($circles as $circle) {
                $circle->notify(new SendSocialMessage(
                    $annText
                ));
            }
        }
    }
}
