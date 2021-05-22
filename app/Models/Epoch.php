<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Epoch extends Model
{
    use HasFactory;

    protected $fillable = ['number','start_date','end_date','circle_id','ended',
        'notified_start','notified_before_end','notified_end','telegram_id','grant'];
    protected $dates = ['start_date','end_date'];

    protected $appends = ['is_regift_phase'];

    public function circle() {
        return $this->belongsTo('App\Models\Circle','circle_id','id');
    }

    public function scopeIsActiveDate($query) {
        $today = Carbon::today()->toDateString();
        return $query->whereDate('start_date', '<=', $today)->whereDate('end_date','>=', $today);
    }

    public function getIsRegiftPhaseAttribute() {

        // check if there is regift day and epoch has not ended
        if($this->regift_days == 0 || $this->ended ) {
            return false;
        }
        $today = Carbon::today();
        $diff = $today->diffInDays($this->end_date, false);
        return $diff <= $this->regift_days ;

    }

}
