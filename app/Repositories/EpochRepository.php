<?php


namespace App\Repositories;
use App\Models\PendingTokenGift;
use App\Models\TokenGift;
use App\Models\Epoch;
use DB;

class EpochRepository
{
    protected $model;

    public function __construct(PendingTokenGift $model) {
        $this->model = $model;
    }

    public function endEpoch($circle_id) {

        $pending_gifts = $this->model->where(function($q) {
            $q->where('tokens','!=', 0)->orWhere('note','!=','');
        })->get();
        $epoch = Epoch::where('circle_id',$circle_id)->orderBy('id','desc')->first();
        $epoch_number = $epoch ? $epoch->number + 1 : 1;
        DB::transaction(function () use ($pending_gifts, $epoch_number, $circle_id) {

            $epoch = new Epoch(['number'=>$epoch_number, 'circle_id' => $circle_id]);
            $epoch->save();
            foreach($pending_gifts as $gift) {
                $tokenGift = new TokenGift($gift->replicate()->toArray());
                $tokenGift->epoch_id = $epoch->id;
                $tokenGift->save();
            }
            $this->model->where('circle_id',$circle_id)->delete();
        });
    }
}
