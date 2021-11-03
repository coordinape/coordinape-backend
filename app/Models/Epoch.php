<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Epoch extends Model
{
    use HasFactory;

    protected $fillable = ['number','start_date','end_date','circle_id','ended',
        'notified_start','notified_before_end','notified_end','telegram_id',
        'grant','days','repeat', 'repeat_day_of_month'];
    protected $dates = ['start_date','end_date'];

    public function circle() {
        return $this->belongsTo('App\Models\Circle','circle_id','id');
    }

    public function scopeIsActiveDate($query) {
        $today = Carbon::today()->toDateString();
        return $query->whereDate('start_date', '<=', $today)->whereDate('end_date','>=', $today);
    }

    public function scopeIsActiveFutureDate($query) {
        $today = Carbon::today()->toDateString();
        return $query->whereDate('end_date','>=', $today);
    }

    public function scopeCheckOverlapDatetime($query, $data) {
        return $query->where('circle_id',$data['circle_id'])->where('start_date', '<=', $data['end_date'])
            ->where('end_date', '>', $data['start_date']);
    }

}
