<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\PendingTokenGift;
use App\Models\Profile;
use App\Models\TokenGift;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\SendSocialMessage;
use App\Models\Circle;
use DB;
use App\Repositories\EpochRepository;
use App\Helper\Utils;

class BotController extends Controller
{

    const yearnCircleId = 1;

    protected $repo;
    public function __construct(EpochRepository $repo) {
        $this->repo = $repo;
    }

    public function webHook(Request $request) {
        $updates = Telegram::getWebhookUpdates();
        $message = $updates->message;
        Log::info($message);
        if(!empty($message) &&
            ((!empty($message['entities'][0]['type']) && ($message['entities'][0]['type']=='bot_command' )) ||
            (!empty($message['text']['entities'][0]['type']) && ($message['text']['entities'][0]['type']=='bot_command' ))
            )
        ) {
            $this->processCommands($message);
        }

       // return response()->json(['success' => 1]);
    }

    private function processCommands($message) {
        $textArray = explode(' ',$message['text']);
        $command = $textArray[0];
        $command = explode('@',$command)[0];
        $is_group = $message['chat']['type'] == 'group' || $message['chat']['type'] == 'supergroup';
        switch($command) {
            case '/start':
                $profile = $this->addProfileChatId($message);
                if($profile) {
                    $profile->notify(new SendSocialMessage(
                        "Congrats {$profile->name} You have successfully registered your Telegram to Coordinape !\nI will occasionally send you important reminders and updates!"
                    ));
                }
                break;
            case '/give':
                $this->give($message, $is_group);
                break;

            case '/regive':
                $this->regive($message, $is_group);
                break;

            case '/ungive':
                $this->ungive($message, $is_group);
                break;

            case '/announce':
                $this->sendAnnouncement($message);
                break;

            case '/gives':
                $this->getAllocs($message, $is_group);
                break;

            case '/receipts':
                $this->getReceipts($message, $is_group);
                break;

            case '/commands':
                $this->getCommands($message, $is_group);
                break;

            case '/discord':
                $this->getDiscord($message, $is_group);
                break;

            case '/website':
                $this->getWebsite($message, $is_group);
                break;

            case '/apply':
                $this->getTypeform($message, $is_group);
                break;

            case '/help':
                $this->getHelp($message, $is_group);
                break;

            case '/feedback':
                $this->feedback($message, $is_group);
                break;
            case '/feedbacks':
                $this->feedbacks($message, $is_group);
                break;

        }
    }

    private function feedbacks($message, $is_group) {
        $circle = $this->getCircle($message, $is_group);
        if($circle) {

            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {

                $feedbacks = Feedback::limit(20)->orderBy('id','desc')->get();
                $fullStr = '';
                foreach($feedbacks as $feedback) {
                    $name = Utils::cleanStr($feedback->telegram_username);
                    $messageStr = Utils::cleanStr($feedback->message);
                    $feedback_no = sprintf('%04d', $feedback->id);
                    $fullStr .= "\n{$feedback_no} {$name} :\n$messageStr\n";
                }

                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username\n$fullStr", false
                ));
            }
        }
    }

    private function feedback($message, $is_group) {
        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {

                $msg = substr($message['text'],10);
                $feedback = new Feedback(['user_id' => $profile->users[0]->id,
                    'telegram_username' => $profile->telegram_username, 'message'=> $msg ]);

                $feedback->save();
                $feedback_no = sprintf('%04d', $feedback->id);
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username your feedback #$feedback_no has been logged "
                ));
            }
        }
    }

    private function getHelp($message, $is_group) {
        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username https://docs.coordinape.com", false
                ));
            }
        }
    }

    private function getDiscord($message,$is_group) {
        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username https://discord.gg/7MWSTamFX4", false
                ));
            }
        }
    }

    private function getWebsite($message,$is_group) {

        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username https://coordinape.com", false
                ));
            }
        }
    }

    private function getTypeform($message,$is_group) {

        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username https://yearnfinance.typeform.com/to/egGYEbrC", false
                ));
            }
        }
    }

    private function getCommands($message, $is_group) {

        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $commands = "/regive - Allocate according to your previous epoch's allocations, your current existing allocations will be reset
/give - Add username, tokens and note (optional) after the command separated by a space e.g /give @zashtoneth 20 thank you note
/gives - Get all the allocations that you have sent
/ungive - Deallocate all your existing tokens that you have given
/receipts - Get all the allocations that you have received
/discord - link to discord
/website - link to website
/apply - typeform link to join coordinape and give out grants through our application
/help - link to documentation
/feedback - please use this to provide feedback/suggestions/bug findings to me
The commands all can be executed in group chats/PM , the bot is exclusively linked to yearn's community circle and usable whenever an epoch is active.
";

                $notifyModel->notify(new SendSocialMessage(
                    $commands, false
                ));
            }
        }
    }

    private function ungive($message, $is_group) {
        if(empty($message['from']['username'])) {
            return false;
        }
        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $user = $profile->users[0];
                if(count($circle->epoches) == 0)
                {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, there is currently no active epochs"
                    ));
                    return false;
                }
                DB::transaction(function () use($user) {
                    $this->repo->resetGifts($user,[]);
                });

                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username $user->name ser, You have deallocated all your tokens, you have now $user->starting_tokens tokens remaining"
                ));
            }
        }
    }

    private function regive($message, $is_group) {

        if(empty($message['from']['username'])) {
           return false;
        }

        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                $user = $profile->users[0];
                if(count($circle->epoches) == 0)
                {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, there is currently no active epochs"
                    ));
                    return false;
                }
                if($user->non_giver) {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, You are not allowed to give allocations"
                    ));
                    return false;
                }

                $latestEpoch = $circle->epoches()->where('epoches.ended',1)->whereNotNull('epoches.number')->orderBy('epoches.number','desc')->first();
                if(!$latestEpoch) {
                   return false;
                }

                $lastEpochGifts = TokenGift::with(['recipient'])->where('sender_id', $user->id)->where('epoch_id',$latestEpoch->id)->get();
                if(count($lastEpochGifts) == 0) {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, you didn't allocate any tokens to anyone in the previous epoch"
                    ));
                    return false;
                }
                $sentSum = $lastEpochGifts->SUM('tokens');
                if($sentSum > $user->starting_tokens) {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, you don't have sufficient starting tokens to give the same allocations"
                    ));
                    return false;
                }
                $epoch_id = $circle->epoches[0]->id;
                DB::transaction(function() use ($lastEpochGifts, $user, $circle, $notifyModel, $epoch_id, $profile) {
                    $totalTokens = 0;
                    $startingTokens = $user->starting_tokens;
                    $this->repo->resetGifts($user,[]);
                    foreach($lastEpochGifts as $epochGift) {
                        $recipientUser = $epochGift->recipient;
                        if($epochGift->recipient) {
                            $tokenGift = new PendingTokenGift($epochGift->replicate(['created_at','updated_at','epoch_id','dts_created'])->toArray());
                            $tokenGift->sender_address = $user->address;
                            $tokenGift->recipient_address = $recipientUser->address;
                            $tokenGift->epoch_id = $epoch_id;
                            if($recipientUser->non_receiver) {
                                $tokenGift->tokens = 0;
                            }
                            $tokenGift->save();
                            $totalTokens += $tokenGift->tokens ;
                            $recipientUser->give_token_received = $recipientUser->pendingReceivedGifts()->get()->SUM('tokens');
                            $recipientUser->save();
                        }
                    }
                    $user->give_token_remaining = $startingTokens - $user->pendingSentGifts()->get()->SUM('tokens');
                    if($user->give_token_remaining < 0) {
                        $notifyModel->notify(new SendSocialMessage(
                            "@$profile->telegram_username Sorry $user->name ser, something went wrong"
                        ));
                        throw new Exception;
                    }
                    $user->save();
                });

                $allocStr = '';
                $pendingSentGifts = $user->pendingSentGifts;
                $sent = 0;

                foreach($pendingSentGifts as $gift) {
                    $name = Utils::cleanStr($gift->recipient->name);
                    $optOutStr = $gift->recipient->non_receiver ? " (Opt Out)":"";
                    $allocStr .= "{$name}$optOutStr > $gift->tokens tokens\n";
                    $sent += $gift->tokens;
                }

                $notifyModel->notify(new SendSocialMessage(
                    "@$profile->telegram_username $user->name ser, you have allocated $sent/$user->starting_tokens of your tokens\n$allocStr", false
                ));
            }
        }
    }

    private function give($message, $is_group) {
        // command @username amount note
        $textArray = explode(' ',$message['text']);
        if(count($textArray) < 3)
            return false;

        if(empty($message['from']['username'])) {
            return false;
        }

        $recipientUsername = substr($textArray[1],1);
        $amount = filter_var($textArray[2], FILTER_VALIDATE_INT) ? (int)($textArray[2]): 0;
        $noteArray = explode($amount, $message['text'],2);
        $note = !empty($noteArray[1]) ? trim($noteArray[1]):'';
        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $user = $profile->users[0];
                $user->load('pendingSentGifts');
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle : $profile;
                if(strtolower($recipientUsername) == strtolower($message['from']['username'])) {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, you can't give to yourself"
                    ));
                    return false;
                }

                if(count($circle->epoches) == 0)
                {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, there is currently no active epochs"
                    ));
                    return false;
                }
                if($user->non_giver) {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, You are not allowed to give allocations"
                    ));
                    return false;
                }
                $recipientProfile = $this->findProfileByTelegramNcircle($recipientUsername, $circle->id);
                if($recipientProfile) {
                    $recipientUser = $recipientProfile->users[0];
                    $noteOnly = false;
                    $optOutText = "";
                    if($recipientUser->non_receiver || $recipientUser->fixed_non_receiver) {
                        $amount = 0;
                        $optOutText = "(Opt Out)";
                    }
                    if($amount == 0 )
                        $noteOnly = true;

                    $epoch_id = $circle->epoches[0]->id;
                    DB::transaction(function () use($user, $recipientUser, $circle, $notifyModel, $amount, $note, $noteOnly, $recipientUsername, $optOutText,$epoch_id, $profile) {
                        $pendingSentGifts = $user->pendingSentGifts;
                        $remainingGives = $user->give_token_remaining;
                        foreach($pendingSentGifts as $gift) {
                            if($gift->recipient_id==$recipientUser->id) {
                                if(($remainingGives + $gift->tokens - $amount) < 0) {
                                    $notifyModel->notify(new SendSocialMessage(
                                        "@$profile->telegram_username Sorry $user->name ser, You only have $remainingGives tokens remaining you're ngmi"
                                    ));
                                    return false;
                                }
                                $current = $gift->tokens;
                                $gift->tokens = $amount;
                                $gift->note = $note;
                                $gift->epoch_id = $epoch_id;
                                if($amount == 0 && !$note)
                                    $gift->delete();
                                else
                                    $gift->save();

                                $recipientUser->give_token_received = $recipientUser->pendingReceivedGifts()->get()->SUM('tokens');
                                $recipientUser->save();
                                $user->give_token_remaining = $user->starting_tokens - $user->pendingSentGifts()->get()->SUM('tokens');
                                $user->save();
                                $notifyModel->notify(new SendSocialMessage(
                                    "@$profile->telegram_username $user->name ser, You have successfully updated your allocated $current tokens for $recipientUser->name @$recipientUsername $optOutText to $amount tokens. You have $user->give_token_remaining tokens remaining"
                                ));
                                return true;
                            }
                        }

                        if($amount == 0 && !$note) {
                            if($optOutText)
                            {
                                $notifyModel->notify(new SendSocialMessage(
                                    "@$profile->telegram_username Sorry $user->name ser, You are only sending tokens to an Opt Out recipient, please include at least a note"
                                ));
                            }
                            return false;
                        }

                        if($amount > $user->give_token_remaining) {
                            $notifyModel->notify(new SendSocialMessage(
                                "@$profile->telegram_username Sorry $user->name ser, You only have $remainingGives tokens remaining you're ngmi"
                            ));
                            return false;
                        }

                        $giftData['sender_id'] = $user->id;
                        $giftData['sender_address'] = $user->address;
                        $giftData['recipient_address'] = $recipientUser->address;
                        $giftData['recipient_id'] = $recipientUser->id;
                        $giftData['tokens'] = $amount;
                        $giftData['circle_id'] = $circle->id;
                        $giftData['epoch_id'] = $epoch_id;
                        $giftData['note'] = $note;
                        $gift = new PendingTokenGift($giftData);
                        $gift->save();
                        $recipientUser->give_token_received = $recipientUser->pendingReceivedGifts()->get()->SUM('tokens');
                        $recipientUser->save();
                        $user->give_token_remaining = $user->starting_tokens - $user->pendingSentGifts()->get()->SUM('tokens');
                        $user->save();
                        $msg = $noteOnly? "@$profile->telegram_username $user->name ser, You have successfully sent a note to $recipientUser->name $optOutText":"@$profile->telegram_username $user->name ser, You have successfully allocated $amount tokens to $recipientUser->name @$recipientUsername. You have $user->give_token_remaining tokens remaining";
                        $notifyModel->notify(new SendSocialMessage(
                            $msg
                        ));
                    });

                } else {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, $recipientUsername does not exist in this circle"
                    ));
                }
            }
        }
    }

    private function getCircle($message, $is_group) {
        $whitelisted = [self::yearnCircleId];
        //$chat_id = $message['chat']['id'];
        $circle = Circle::with(['epoches' => function ($q) {
            $q->isActiveDate();
        }])->whereIn('id',$whitelisted)->first();
//        $circle = $is_group ? Circle::with(['epoches' => function ($q) {
//            $q->isActiveDate();
//        }])->where('telegram_id', $chat_id)->whereIn('id',$whitelisted)->first(): Circle::with(['epoches' => function ($q) {
//            $q->isActiveDate();
//        }])->whereIn('id',$whitelisted)->first();

        return $circle;
    }

    private function getAllocs($message, $is_group = false) {

        if(empty($message['from']['username'])) {
            return false;
        }

        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $user = $profile->users[0];
                $user->load('pendingSentGifts.recipient');
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                if(count($circle->epoches) == 0)
                {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, there is currently no active epochs"
                    ));
                    return false;
                }
                $allocStr = '';
                $pendingSentGifts = $user->pendingSentGifts;
                $sent = 0;
                foreach($pendingSentGifts as $gift) {
                    $name = Utils::cleanStr($gift->recipient->name);
                    $optOutStr = $gift->recipient->non_receiver ? " (Opt Out)":"";
                    $allocStr .= "{$name}$optOutStr > $gift->tokens tokens\n";
                    $sent += $gift->tokens;
                }
                if(!$allocStr)
                    $allocStr = "@$profile->telegram_username You have sent no allocations currently";
                else
                    $allocStr = "@$profile->telegram_username Allocations: $sent\n$allocStr";

                $notifyModel->notify(new SendSocialMessage(
                    $allocStr
                ));
            }
        }

    }

    private function getReceipts($message, $is_group = false) {

        if(empty($message['from']['username'])) {
            return false;
        }
        $circle = $this->getCircle($message, $is_group);
        if($circle) {
            $profile = $this->findProfileByTelegramNcircle($message['from']['username'], $circle->id);
            if($profile) {
                $user = $profile->users[0];
                $user->load('pendingReceivedGifts.sender');
                $profile = $this->checkForProfileChatId($profile,$message);
                $notifyModel = $is_group ? $circle:$profile;
                if(count($circle->epoches) == 0)
                {
                    $notifyModel->notify(new SendSocialMessage(
                        "@$profile->telegram_username Sorry $user->name ser, there is currently no active epochs"
                    ));
                    return false;
                }
                $allocStr = '';
                $pendingReceivedGifts = $user->pendingReceivedGifts;
                foreach($pendingReceivedGifts as $gift) {
                    $allocStr .= "{$gift->sender->name} > $gift->tokens tokens\n";
                }
                if(!$allocStr)
                    $allocStr = "@$profile->telegram_username You received no allocations currently";
                else
                    $allocStr = "@$profile->telegram_username Received\n$allocStr";

                $notifyModel->notify(new SendSocialMessage(
                    $allocStr
                ));
            }
        }
    }

    private function sendAnnouncement($message) {

        $annText = substr($message['text'],10);
        $profile = $this->findProfileByTelegramNcircle($message['from']['username'])->first();

        if($profile && $profile->ann_power) {
            $circles = Circle::whereNotNull('telegram_id')->orWhereNotNull('discord_webhook')->get();
            foreach($circles as $circle) {
                $circle->notify(new SendSocialMessage(
                    $annText, true
                ));
            }
        }
    }

    private function addProfileChatId($message) {
        if(empty($message['from']['username'])) {
            return false;
        }
        $is_private = $message['chat']['type'] == 'private';
        if($is_private) {
            $profile = Profile::leftJoin('users','users.address','profiles.address')
                        ->where('profiles.telegram_username', $message['from']['username'])
                        ->select(['profiles.*','users.circle_id','users.name'])
                        ->first();
            if(!$profile) {
                return false;
            } else {
                $profile->update(['chat_id' => $message['chat']['id']]);
                return $profile;
            }
        }

        return false;
    }

    private function findProfileByTelegramNcircle($telegram_username, $circle_id = null) {
        $query =  Profile::leftJoin('users','profiles.address','users.address')
                    ->where('profiles.telegram_username', $telegram_username);
        if($circle_id)
            $query->where('users.circle_id',$circle_id);

        return $query->first();
    }

    private function checkForProfileChatId($profile,$message) {
        $is_private = $message['chat']['type'] == 'private';
        if($is_private && !$profile->chat_id) {
            $result = $this->addProfileChatId($message);
            return $result ?:$profile;
        }

        return $profile;
    }
}
