<?php

namespace App\Http\Controllers;

use App\Models\PendingTokenGift;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\SendSocialMessage;
use App\Models\Circle;
use DB;

class BotController extends Controller
{

    const yearnCircleId = 1;

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
                $this->give($message, $message['chat']['type'] == 'group');
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
                $this->give($message, $message['chat']['type'] == 'group');
                break;

            case '/announce':
               $this->sendAnnouncement($message);
             break;
        }
    }

    private function give($message, $is_group) {
        // command @username amount note
        $textArray = explode(' ',$message['text']);
        if(count($textArray) < 3)
            return false;

        $recipientUsername = substr($textArray[1],1);
        $amount = filter_var($textArray[2], FILTER_VALIDATE_INT) ? (int)($textArray[2]): 0;
        $note = !empty($textArray[3]) ? $textArray[3]:'';
        $whitelisted = [self::yearnCircleId];
        $chat_id = $message['chat']['id'];
        $circle = $is_group ? Circle::with(['epoches' => function ($q) {
            $q->isActiveDate();
        }])->where('telegram_id', $chat_id)->whereIn('id',$whitelisted)->first(): Circle::with(['epoches' => function ($q) {
            $q->isActiveDate();
        }])->whereIn('id',$whitelisted)->first();
        Log::info($circle);
        if($circle) {
            $user = User::with('pendingSentGifts')->where('telegram_username', $message['from']['username'])->where('circle_id',$circle->id)->first();
            Log::info($user);
            if($user) {
                $notifyModel = $is_group ? $circle : $user;
                if(count($circle->epoches) == 0)
                {
                    $notifyModel->notify(new SendSocialMessage(
                        "Sorry $user->name ser, there is currently no active epochs"
                    ));
                    return false;
                }
                if($user->non_giver) {
                    $notifyModel->notify(new SendSocialMessage(
                        "Sorry $user->name ser, You are not allowed to give allocations"
                    ));
                    return false;
                }
                $recipientUser = User::where('telegram_username',$recipientUsername)->where('circle_id', $circle->id)->first();
                Log::info($recipientUser);
                if($recipientUser) {
                    $noteOnly = false;
                    if($recipientUser->non_receiver || $recipientUser->fixed_non_receiver) {
                        $amount = 0;
                    }
                    if($amount == 0 )
                        $noteOnly = true;

                    DB::transaction(function () use($user, $recipientUser, $circle, $notifyModel, $amount, $note, $noteOnly) {
                        $pendingSentGifts = $user->pendingSentGifts;
                        $remainingGives = $user->give_token_remaining;
                        foreach($pendingSentGifts as $gift) {
                            if($gift->recipient_id==$recipientUser->id) {
                                if(($remainingGives + $gift->tokens - $amount) < 0) {
                                    $notifyModel->notify(new SendSocialMessage(
                                        "Sorry $user->name ser, You only have $remainingGives tokens remaining you're ngmi"
                                    ));
                                    return false;
                                }
                                $current = $gift->tokens;
                                $gift->tokens = $amount;
                                if($note)
                                    $gift->note = $note;

                                $gift->save();
                                $recipientUser->give_token_received = $recipientUser->pendingReceivedGifts()->get()->SUM('tokens');
                                $recipientUser->save();
                                $user->give_token_remaining = $user->starting_tokens - $user->pendingSentGifts()->get()->SUM('tokens');
                                $user->save();
                                $notifyModel->notify(new SendSocialMessage(
                                    "$user->name ser, You have successfully updated your allocated $current tokens for $recipientUser->name to $amount tokens. You have $user->give_token_remaining tokens remaining"
                                ));
                                return true;
                            }
                        }

                        if($amount == 0 && !$note)
                            return false;

                        if($amount > $user->give_token_remaining) {
                            $notifyModel->notify(new SendSocialMessage(
                                "Sorry $user->name ser, You only have $remainingGives tokens remaining you're ngmi"
                            ));
                            return false;
                        }

                        $giftData['sender_id'] = $user->id;
                        $giftData['sender_address'] = $user->address;
                        $giftData['recipient_address'] = $recipientUser->address;
                        $giftData['recipient_id'] = $recipientUser->id;
                        $giftData['tokens'] = $amount;
                        $giftData['circle_id'] = $circle->id;
                        $giftData['note'] = $note;
                        $gift = new PendingTokenGift($giftData);
                        $gift->save();
                        $recipientUser->give_token_received = $recipientUser->pendingReceivedGifts()->get()->SUM('tokens');
                        $recipientUser->save();
                        $user->give_token_remaining = $user->starting_tokens - $user->pendingSentGifts()->get()->SUM('tokens');
                        $user->save();
                        $message = $noteOnly? "You have successfully sent a note to $recipientUser->name":"$user->name ser, You have successfully allocated $amount tokens to $recipientUser->name. You have $user->give_token_remaining tokens remaining";
                        $notifyModel->notify(new SendSocialMessage(
                            $message
                        ));
                    });

                } else {
                    $circle->notify(new SendSocialMessage(
                        "Sorry $user->name ser, $recipientUsername does not exist in this circle"
                    ));
                }
            }
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
