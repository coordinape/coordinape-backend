<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nominee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'nominated_by_user_id',
        'description',
        'nominated_date',
        'expiry_date',
        'vouches_required',
        'ended',
        'circle_id'
    ];
    protected $searchable = [
        'id',
        'circle_id',
        'address',
        'nominated_by_user_id',
        'ended'
    ];

    protected $dates = ['expiry_date','nominated_date'];

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }

    public function nominations() {
        return $this->belongsToMany('App\Models\User','vouches','nominee_id','voucher_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function circle() {
        return $this->belongsTo('App\Models\Circle','circle_id');
    }

    public function scopePastExpiryDate($query) {
        $today = Carbon::today()->toDateString();
        return $query->whereDate('expiry_date','<=', $today);
    }
}
