<?php


namespace App\Repositories;
use App\Models\Burn;
use App\Models\PendingTokenGift;
use App\Models\Teammate;
use App\Models\TokenGift;
use App\Models\Epoch;
use App\Notifications\BotLaunch;
use App\Notifications\DailyUpdate;
use App\Notifications\EpochAlmostEnd;
use App\Notifications\EpochStart;
use App\Notifications\OptOutEpoch;
use App\Notifications\SendSocialMessage;
use DB;
use App\Models\User;
use Carbon\Carbon;
use App\Helper\Utils;
use App\Notifications\EpochEnd;
use Exception;
use Illuminate\Database\Eloquent\Model;

class EpochRepository
{
    protected $model;

    public function __construct(PendingTokenGift $model) {
        $this->model = $model;
    }

    public function endEpoch($circle_id) {

        $now = Carbon::now();
        $epoch = Epoch::with('circle')->where('ended',0)->where('circle_id',$circle_id)->where('end_date','<=', $now)->orderBy('id','desc')->first();
        if($epoch) {
            $pending_gifts = $this->model->where(function($q) {
                $q->where('tokens','!=', 0)->orWhere('note','!=','');
            })->where('circle_id',$circle_id)->get();
            $epoch_number = Epoch::where('ended',1)->where('circle_id',$circle_id)->count();
            $epoch_number = $epoch_number + 1;
            $circle = $epoch->circle;
            $unalloc_users = $circle->users()->where('non_giver',0)->yetToSend()->get();
//            $regifted_users = $circle->users()->where('give_token_received','>',0)->where('regift_percent','>',0)->get();
            DB::transaction(function () use ($pending_gifts, $epoch, $circle_id, $epoch_number) {
//                foreach($regifted_users as $regifted_user) {
//                    $burn = new Burn();
//                    $burn['regift_percent'] = $regifted_user->regift_percent;
//                    $burn['tokens_burnt'] = $regifted_user->regift_percent == 100 ? $regifted_user->give_token_received: ceil( $regifted_user->give_token_received / 100 * $regifted_user->regift_percent);
//                    $burn['original_amount'] = $regifted_user->give_token_received;
//                    $burn['circle_id'] = $circle_id;
//                    $burn['epoch_id'] = $epoch->id;
//                    $burn['user_id'] = $regifted_user->id;
//                    $burn->save();
//                }

                foreach($pending_gifts as $gift) {
                    $tokenGift = new TokenGift($gift->replicate()->toArray());
                    $tokenGift->epoch_id = $epoch->id;
                    $tokenGift->save();
                }

                $this->model->where('circle_id',$circle_id)->delete();
                User::where('circle_id',$circle_id)->where('non_giver',0)->yetToSend()->update(['non_receiver'=>1]);
                User::where('circle_id',$circle_id)->update(['give_token_received'=>0, 'give_token_remaining'=>DB::raw("`starting_tokens`"), 'epoch_first_visit' => 1]);
                $epoch->ended = 1;
                $epoch->number = $epoch_number;
                $epoch->save();

            });
            if(!$epoch->notified_end && $circle->telegram_id) {
                $protocol = $circle->protocol;
                $circle_name = $protocol->name.'/'.$circle->name;
                $circle->notify(new EpochEnd($epoch_number,$circle_name,$unalloc_users));
                $epoch->notified_end = Carbon::now();
                $epoch->save();
                Utils::purgeCache($circle_id);
            }
        }
    }

    public function resetGifts($user, $toKeep) {
        $existingGifts = $user->pendingSentGifts()->with('recipient')->whereNotIn('recipient_id',$toKeep)->get();
        foreach($existingGifts as $existingGift) {
            $rUser = $existingGift->recipient;
            $existingGift->delete();
            $rUser->give_token_received = $rUser->pendingReceivedGifts()->get()->SUM('tokens');
            $rUser->save();
        }
        $token_used = $user->pendingSentGifts()->get()->SUM('tokens');
        $user->give_token_remaining = $user->starting_tokens-$token_used;
        $user->save();
    }

    public function getEpochCsv($epoch, $circle_id, $grant=0) {

//        $client = new \GuzzleHttp\Client();
//        $response = $client->get( "https://api.coingecko.com/api/v3/simple/price?ids=yearn-finance&vs_currencies=usd"
//        );
//
//        $ret = json_decode( (string)$response->getBody());
//        $yfi_price = $ret->{'yearn-finance'}->usd;
        $end_date = $epoch->end_date;
        $users = User::with(['receivedGifts' => function ($q) use($epoch, $circle_id) {
            $q->where('epoch_id',$epoch->id)->where('circle_id',$circle_id);
        }, 'sentGifts' => function ($q) use($epoch, $circle_id) {
            $q->where('epoch_id',$epoch->id)->where('circle_id',$circle_id);
        }, 'burns' => function ($q) use ($epoch, $circle_id) {
            $q->where('epoch_id', $epoch->id);
        }])->withTrashed()->where(function($q) use($end_date) {
            $q->whereNull('deleted_at')->orWhere('deleted_at','>',$end_date);
        })->where('circle_id',$circle_id)->where('is_hidden',0)->orderBy('name','asc')->get();

        $grant = $grant ?:$epoch->grant;
        $header = ['No.','name','address','received','sent','epoch number', 'Date'];
        if($grant && $grant>0) {
            $header[] = 'Grant Amt ($)';
        }

        $list = [];
        $list[]= $header;
        $total_sent = TokenGift::where('epoch_id',$epoch->id)->where('circle_id',$circle_id)->get()->SUM('tokens');
        $date_range = $epoch->start_date->format('Y/m/d') . ' - ' . $epoch->end_date->format('Y/m/d');
        foreach($users as $idx=>$user) {
            $received = $user->receivedGifts->SUM('tokens');
            $burn_obj = count($user->burns) ? $user->burns[0]: null;
            $burnt = $burn_obj ? $burn_obj->tokens_burnt : 0;
//            $initial_received = $burn_obj ? $burn_obj->original_amount : $received;
            $usd_received = $grant && $received ? (floor(($received * $grant / $total_sent) * 100) / 100):0;
            $col = [];
            $col[] = $idx +1;
            $col[]= $user->name;
            $col[]= $user->address;
            $col[]= $received - $burnt;
            $col[]= $user->sentGifts->SUM('tokens');
//            $col[]= $burnt;
//            $col[]= $initial_received;
            $col[]= $epoch->number;
            $col[]= $date_range;
            if($grant && $grant>0)
                $col[] = $usd_received ;
            $list[]= $col;
        }

        $protocol = $epoch->circle->protocol;
        $headers = [
               'Content-type'        => "text/csv"
           ,   'Content-Disposition' => "attachment; filename={$protocol->name}-{$epoch->circle->name}-{$epoch->number}.csv"
        ];

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function updateGifts($request, $address) {

        $user = $request->user;
        $gifts = $request->gifts;

        $addresses = [];
        foreach($gifts as $gift) {
            $addresses[] = strtolower($gift['recipient_address']);
        }

        $users = User::where('circle_id',$request->circle_id)->where('is_hidden',0)->whereIn(DB::raw('lower(address)'),$addresses)->get()->keyBy('address');
        $pendingSentGiftsMap = $user->pendingSentGifts()->get()->keyBy('recipient_id');
        DB::transaction(function () use ($users, $user, $gifts, $address, $pendingSentGiftsMap,$epoch_id) {
            $token_used = 0;
            $toKeep = [];
            foreach ($gifts as $gift) {
                $recipient_address = strtolower($gift['recipient_address']);
                if ($users->has($recipient_address)) {
                    if ($user->id == $users[$recipient_address]->id)
                        continue;

                    if($users[$recipient_address]->non_receiver == 1 || $users[$recipient_address]->fixed_non_receiver == 1) {
                        $gift['tokens'] = 0;
                    }

                    $gift['sender_id'] = $user->id;
                    $gift['sender_address'] = strtolower($address);
                    $gift['recipient_address'] = $recipient_address;
                    $gift['recipient_id'] = $users[$recipient_address]->id;
                    $gift['epoch_id'] = $epoch_id;
                    $token_used += $gift['tokens'];
                    $pendingGift = $pendingSentGiftsMap->has($gift['recipient_id']) ? $pendingSentGiftsMap[$gift['recipient_id']] : null  ;

                    if ($pendingGift) {
                        if ($gift['tokens'] == 0 && $gift['note'] == '') {
                            $pendingGift->delete();

                        } else {
                            $pendingGift->tokens = $gift['tokens'];
                            $pendingGift->note = $gift['note'];
                            $pendingGift->save();
                        }
                    } else {
                        if ($gift['tokens'] == 0 && $gift['note'] == '')
                            continue;

                        $pendingGift = $user->pendingSentGifts()->create($gift);
                    }

                    $toKeep[] = $pendingGift->recipient_id;
                    $users[$recipient_address]->give_token_received = $users[$recipient_address]->pendingReceivedGifts()->get()->SUM('tokens');
                    $users[$recipient_address]->save();
                }
            }
            $this->resetGifts($user, $toKeep);
        },2);
    }

    public function newUpdateGifts($request, $address) {

        $user = $request->user;
        $gifts = $request->gifts;

        $ids = [];
        foreach($gifts as $gift) {
            $ids[] = $gift['recipient_id'];
        }

        $users = User::where('circle_id',$request->circle_id)->where('is_hidden',0)->whereIn('id',$ids)->get()->keyBy('id');
        $activeEpoch = $user->circle->epoches()->isActiveDate()->first();
        $epoch_id = $activeEpoch->id;
        $pendingSentGiftsMap = $user->pendingSentGifts()->get()->keyBy('recipient_id');
        DB::transaction(function () use ($users, $user, $gifts, $ids, $pendingSentGiftsMap, $address, $epoch_id) {
            $token_used = 0;
            foreach ($gifts as $gift) {
                $recipient_id = $gift['recipient_id'];
                if ($users->has($recipient_id)) {
                    if ($user->id == $recipient_id)
                        continue;

                    $recipient = $users[$recipient_id];
                    if($recipient->non_receiver == 1 || $recipient->fixed_non_receiver == 1) {
                        $gift['tokens'] = 0;
                    }

                    $gift['sender_id'] = $user->id;
                    $gift['sender_address'] = strtolower($address);
                    $gift['recipient_address'] = $users[$recipient_id]->address;
                    $gift['recipient_id'] = $recipient_id;
                    $gift['epoch_id'] = $epoch_id;

                    $token_used += $gift['tokens'];
                    $pendingGift = $pendingSentGiftsMap->has($gift['recipient_id']) ? $pendingSentGiftsMap[$gift['recipient_id']] : null  ;

                    if ($pendingGift) {
                        if ($gift['tokens'] == 0 && $gift['note'] == '') {
                            $pendingGift->delete();

                        } else {
                            $pendingGift->tokens = $gift['tokens'];
                            $pendingGift->note = $gift['note'];
                            $pendingGift->epoch_id = $epoch_id;
                            $pendingGift->save();
                        }
                    } else {
                        if ($gift['tokens'] == 0 && $gift['note'] == '')
                            continue;

                        $pendingGift = $user->pendingSentGifts()->create($gift);
                    }
                    $toKeep[] = $pendingGift->recipient_id;
                    $recipient->give_token_received = $recipient->pendingReceivedGifts()->get()->SUM('tokens');
                    $recipient->save();
                }
            }

            $token_used = $user->pendingSentGifts()->get()->SUM('tokens');
            if($token_used > $user->starting_tokens) {
                throw new Exception;
            } else {
//                $this->resetGifts($user, $toKeep);
                $user->give_token_remaining = $user->starting_tokens-$token_used;
                $user->save();
            }
        },2);
    }

    public function removeAllPendingGiftsReceived($user, $updateData = []) {
        $pendingGifts = $user->pendingReceivedGifts;
        $pendingGifts->load(['sender.pendingSentGifts']);
        return DB::transaction(function () use ($user, $updateData, $pendingGifts) {
           $optOutStr = "";
           if( (!empty($updateData['fixed_non_receiver']) && $updateData['fixed_non_receiver'] != $user->fixed_non_receiver && $updateData['fixed_non_receiver'] == 1) ||
               (!empty($updateData['non_receiver']) && $updateData['non_receiver'] != $user->non_receiver && $updateData['non_receiver'] == 1)
           )
           {
               $totalRefunded = 0;
               foreach($pendingGifts as $gift) {
                   if(!$gift->tokens && $gift->note)
                       continue;

                   $sender = $gift->sender;
                   $gift_token = $gift->tokens;
                   $totalRefunded += $gift_token;
                   $optOutStr .= "$sender->name: $gift_token\n";
                   $gift->delete();
                   $token_used = $sender->pendingSentGifts->SUM('tokens') - $gift_token;
                   $sender->give_token_remaining = $sender->starting_tokens-$token_used;
                   $sender->save();
               }
               $updateData['give_token_received'] = 0;
               $circle = $user->circle;
               if($circle->telegram_id)
               {
                   $circle->notify(new OptOutEpoch($user,$totalRefunded, $optOutStr));
               }
           }

            $user->update($updateData);
            return $user;
        });
    }

    public function deleteUser($user) {
        $pendingGifts = $user->pendingReceivedGifts;
        $pendingGifts->load(['sender.pendingSentGifts']);
        $existingGifts = $user->pendingSentGifts()->with('recipient')->get();

        return DB::transaction(function () use ($user, $pendingGifts, $existingGifts) {
            foreach($existingGifts as $existingGift) {
                $rUser = $existingGift->recipient;
                $existingGift->delete();
                $rUser->give_token_received = $rUser->pendingReceivedGifts()->get()->SUM('tokens');
                $rUser->save();
            }
            foreach($pendingGifts as $gift) {
                $sender = $gift->sender;
                $gift_token = $gift->tokens;
                $gift->delete();
                $token_used = $sender->pendingSentGifts->SUM('tokens') - $gift_token;
                $sender->give_token_remaining = $sender->starting_tokens-$token_used;
                $sender->save();
            }

            Teammate::where('team_mate_id', $user->id)->delete();
            Teammate::where('user_id', $user->id)->delete();
            $user->delete();
            return $user;
        },2);
    }

    public function checkEpochNotifications($epoch) {
        if(!$epoch->notified_start) {
            $circle = $epoch->circle;
            $protocol = $circle->protocol;
            $circle_name = $protocol->name.'/'.$circle->name;
            $circle->notify(new EpochStart($epoch,$circle_name,$circle));
            if($circle->id == 1)
                $circle->notify(new BotLaunch());
            if($protocol->telegram_id) {
                $protocol->notify(new EpochStart($epoch,$circle_name,$circle));
                if($circle->id == 1)
                    $protocol->notify(new BotLaunch());
            }
            $epoch->notified_start = Carbon::now();
            $epoch->save();
        }
        else if(!$epoch->notified_before_end) {
            $now = Carbon::now()->addDays(1);
            if($epoch->end_date <= $now) {
                $circle = $epoch->circle;
                $unalloc_users = $circle->users()->where('non_giver',0)->where('is_hidden',0)->where('give_token_remaining','>',0)->get();
                $protocol = $circle->protocol;
                $circle_name = $protocol->name.'/'.$circle->name;
                $circle->notify(new EpochAlmostEnd($circle_name,$unalloc_users));
                if($protocol->telegram_id) {
                    $protocol->notify(new EpochAlmostEnd($circle_name,$unalloc_users));
                }
                $epoch->notified_before_end = Carbon::now();
                $epoch->save();

                foreach($unalloc_users->chunk(20) as $chunk) {
                    foreach($chunk as $unalloc_user) {
                        if($unalloc_user->chat_id) {
                            $unalloc_user->notify(new SendSocialMessage("You still have $unalloc_user->give_token_remaining tokens remaining in $circle_name !\nDo use them before the epoch ends in 24 hours\nYou can also allocate via Telegram /commands to see how !", false));
                        }
                    }
                    sleep(1);
                }
            }
        }
    }

    public function dailyUpdate($epoch) {

        $circle = $epoch->circle;
        $users = $circle->users()->where('is_hidden',0)->get();
        $pending_gifts = $circle->pending_gifts;
        $total_gifts_sent = count($pending_gifts);
        $total_tokens_sent = $pending_gifts->SUM('tokens');
        $opt_outs = $circle->users()->where('is_hidden',0)->optOuts()->count();
        $has_sent = $circle->users()->where('is_hidden',0)->hasSent()->count();
        $total_users = count($users);
        $sent_today_gifts = $circle->pending_gifts()->with('sender')->sentToday()->get();

        $name_strs = '';
        $user_added = [];
        foreach($sent_today_gifts as $pending_gift) {

            if(array_key_exists($pending_gift->sender_id,$user_added))
                continue;

            $sender = $pending_gift->sender;
            if($name_strs)
                $name_strs .= ', ';

            $name_strs .= $sender->telegram_username ?: Utils::cleanStr($sender->name);
            $user_added[$pending_gift->sender_id] = true;
        }
        $epoch_num = Epoch::where('circle_id',$circle->id)->where('ended', 1)->count();
        $epoch_num += 1;
        $protocol = $circle->protocol;
        $circle_name = $protocol->name.'/'.$circle->name;
        $circle->notify(new DailyUpdate($epoch, $name_strs, $total_gifts_sent, $total_tokens_sent, $opt_outs, $has_sent, $total_users,$epoch_num,$circle_name));
        if($protocol->telegram_id && $circle->id!=5) {
            $protocol->notify(new DailyUpdate($epoch, $name_strs, $total_gifts_sent, $total_tokens_sent, $opt_outs, $has_sent, $total_users,$epoch_num,$circle_name));
        }
    }
}
