<?php

namespace App\Models;

use Exception;
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
        'min_vouches_percent',
        'calculate_vouching_percent',
        'nomination_days_limit',
        'vouching_text',
        'logo',
        'default_opt_in',
        'team_selection',
        'discord_webhook',
        'only_giver_vouch',
        'is_verified',
        'auto_opt_out'
    ];
    protected $searchable = [
        'protocol_id',
        'id'
    ];

    protected $hidden = ['discord_webhook','telegram_id'];

    public function routeNotificationForTelegram()
    {
        if(config('app.domain') != 'coordinape.me')
            return '-573708082';

        return $this->telegram_id;
    }

    public function routeNotificationForDiscord()
    {
        if(config('services.discord.test-webhook')) {
            return config('services.discord.test-webhook');
        } else if(config('app.env') == 'local') {
            throw new Exception('Please set TEST_DISCORD_WEBHOOK in your .env ');
        }

        return $this->discord_webhook;
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
