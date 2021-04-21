<?php


namespace App\Repositories;
use App\Models\PendingTokenGift;
use App\Models\TokenGift;
use App\Models\Epoch;
use DB;
use App\Models\User;
use Carbon\Carbon;
use App\Helper\Utils;

class EpochRepository
{
    protected $model;

    public function __construct(PendingTokenGift $model) {
        $this->model = $model;
    }

    public function endEpoch($circle_id) {

        $now = Carbon::now();
        $epoch = Epoch::where('ended',0)->where('circle_id',$circle_id)->where('end_date','<=', $now)->orderBy('id','desc')->first();
        if($epoch) {
            $pending_gifts = $this->model->where(function($q) {
                $q->where('tokens','!=', 0)->orWhere('note','!=','');
            })->where('circle_id',$circle_id)->get();
            $epoch_number = Epoch::where('ended',1)->where('circle_id',$circle_id)->count();
            $epoch_number = $epoch_number + 1;
            DB::transaction(function () use ($pending_gifts, $epoch, $circle_id, $epoch_number) {
                foreach($pending_gifts as $gift) {
                    $tokenGift = new TokenGift($gift->replicate()->toArray());
                    $tokenGift->epoch_id = $epoch->id;
                    $tokenGift->save();
                }

                $this->model->where('circle_id',$circle_id)->delete();
                User::where('circle_id',$circle_id)->where('non_giver',0)->where('give_token_remaining',100)->update(['non_receiver'=>1]);
                User::where('circle_id',$circle_id)->update(['give_token_received'=>0, 'give_token_remaining'=>100, 'epoch_first_visit' => 1]);
                $epoch->ended = 1;
                $epoch->number = $epoch_number;
                $epoch->save();
            });
            Utils::purgeCache($circle_id);
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
        $user->give_token_remaining = 100-$token_used;
        $user->save();
    }

    public function getEpochCsv($epoch, $circle_id, $grant=0) {

//        $client = new \GuzzleHttp\Client();
//        $response = $client->get( "https://api.coingecko.com/api/v3/simple/price?ids=yearn-finance&vs_currencies=usd"
//        );
//
//        $ret = json_decode( (string)$response->getBody());
//        $yfi_price = $ret->{'yearn-finance'}->usd;

        $users = User::with(['receivedGifts' => function ($q) use($epoch, $circle_id) {
            $q->where('epoch_id',$epoch->id)->where('circle_id',$circle_id);
        }, 'sentGifts' => function ($q) use($epoch, $circle_id) {
            $q->where('epoch_id',$epoch->id)->where('circle_id',$circle_id);
        }])->where('circle_id',$circle_id)->where('is_hidden',0)->orderBy('name','asc')->get();

        $header = ['No.','name','address','received','sent','epoch number', 'Daate'];
        $list = [];
        $list[]= $header;
        //$total_sent = TokenGift::where('epoch_id',$epoch->id)->where('circle_id',$circle_id)->get()->SUM('tokens');
        $date_range = $epoch->start_date->format('Y/m/d') . ' - ' . $epoch->end_date->format('Y/m/d');
        foreach($users as $idx=>$user) {
            $received = $user->receivedGifts->SUM('tokens');
//            $usd_received = $grant && $received ? (floor(($received * $grant / $total_sent) * 100) / 100):0;
//            $yfi_received = $usd_received ? $usd_received/ $yfi_price : 0;
            $col = [];
            $col[] = $idx +1;
            $col[]= $user->name;
            $col[]= $user->address;
            $col[]= $received;
            $col[]= $user->sentGifts->SUM('tokens');
            $col[]= $epoch->number;
            $col[]= $date_range;
//            $col[] = $usd_received ;
//            $col[] = $yfi_received ;
            $list[]= $col;
        }

        $headers = [
               'Content-type'        => 'text/csv'
           ,   'Content-Disposition' => 'attachment; filename=receipts.csv'
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

    public function removeAllPendingGiftsReceived($user, $updateData = []) {
        $pendingGifts = $user->pendingReceivedGifts;
        $pendingGifts->load(['sender.pendingSentGifts']);
        return DB::transaction(function () use ($user, $updateData, $pendingGifts) {
           if(!empty($updateData['non_receiver']) && $updateData['non_receiver'] != $user->non_receiver && $updateData['non_receiver'] == 1)
           {
               foreach($pendingGifts as $gift) {
                   if(!$gift->tokens && $gift->note)
                       continue;

                   $sender = $gift->sender;
                   $gift_token = $gift->tokens;
                   $gift->delete();
                   $token_used = $sender->pendingSentGifts->SUM('tokens') - $gift_token;
                   $sender->give_token_remaining = 100-$token_used;
                   $sender->save();
               }
               $updateData['give_token_received'] = 0;
           }
            $user->update($updateData);
            return $user;
        });
    }
}
