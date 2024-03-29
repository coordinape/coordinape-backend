<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $searchable = [
      'circle_id',
      'address',
      'id',
      'non_giver'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'circle_id',
        'give_token_received',
        'give_token_remaining',
        'bio',
        'non_receiver',
        'epoch_first_visit',
        'non_giver',
        'starting_tokens',
        'fixed_non_receiver',
        'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    public function scopeProtocolFilter($query, $filters) {

        $query->leftJoin('circles as c','c.id', 'users.circle_id');
        $query->leftJoin('protocols as p','c.protocol_id', 'p.id');
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where("users.$key", $filter);
            }
        }
        $query->where('c.protocol_id', $filters['protocol_id']);
        $query->select(['users.name',
            'users.address',
            'users.circle_id',
            'users.give_token_received',
            'users.give_token_remaining',
            'users.bio',
            'users.non_receiver',
            'users.epoch_first_visit',
            'users.non_giver', 'c.protocol_id',
            'users.role',
            'users.starting_tokens']);

        return $query;
    }

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }

    public function routeNotificationForTelegram()
    {
        return $this->chat_id;
    }

    public function scopeYetToSend($query) {
        return $query->whereColumn('give_token_remaining','starting_tokens');
    }

    public function scopeHasSent($query) {
        return $query->whereColumn('give_token_remaining','<','starting_tokens');
    }

    public function scopeIsAdmin($query) {
        return $query->where('role', config('enums.user_types.admin'));
    }

    public function scopeByAddress($query, $address) {
        return $query->where( 'address',  strtolower($address));
    }

    public function scopeOptOuts($query) {
        return $query->where('non_receiver',1);
    }

    public function pendingSentGifts() {
        return $this->hasMany('App\Models\PendingTokenGift','sender_id','id');
    }

    public function pendingReceivedGifts() {
        return $this->hasMany('App\Models\PendingTokenGift','recipient_id','id');
    }

    public function receivedGifts() {
        return $this->hasMany('App\Models\TokenGift','recipient_id','id');
    }

    public function sentGifts() {
        return $this->hasMany('App\Models\TokenGift','sender_id','id');
    }

    public function teammates() {
        return $this->belongsToMany('App\Models\User','teammates','user_id','team_mate_id');
    }

    public function circle() {
        return $this->belongsTo('App\Models\Circle','circle_id','id');
    }

    public function burns() {
        return $this->hasMany('App\Models\Burn', 'user_id','id');
    }

    public function histories() {
        return $this->hasMany('App\Models\History', 'user_id','id');
    }

    public function profile() {
        return $this->belongsTo('App\Models\Profile','address','address');
    }

    public function nominations() {
        return $this->belongsToMany('App\Models\Nominee','vouches','voucher_id','nominee_id');
    }

    public function getIsCoordinapeUserAttribute() {
        return $this->role == config('enums.user_types.coordinape');
    }
}
