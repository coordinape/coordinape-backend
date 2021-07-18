<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Circle extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'protocol_id',
        'token_name',
        'team_sel_text',
        'alloc_text',
        'vouching',
        'min_vouches',
        'nomination_days_limit',
        'vouching_text'
    ];
    protected $searchable = [
        'protocol_id',
    ];

    public function routeNotificationForTelegram()
    {
        if(env('APP_DOMAIN') != 'coordinape.me')
            return '-573708082';

        return $this->telegram_id;
    }

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }
    public function protocol() {
        return $this->belongsTo('App\Models\Protocol','protocol_id','id');
    }

    public function epoches() {
        return $this->hasMany('App\Models\Epoch', 'circle_id', 'id');
    }

    public function users() {
        return $this->hasMany('App\Models\User', 'circle_id', 'id');
    }

    public function pending_gifts() {
        return $this->hasMany('App\Models\PendingTokenGift', 'circle_id', 'id');
    }

    public function nominees() {
        return $this->hasMany('App\Models\Nominee','circle_id');
    }
}
