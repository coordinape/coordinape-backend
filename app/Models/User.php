<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $searchable = [
      'circle_id',
      'address',
      'id'
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
        'avatar'
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
//    protected $casts = [
//        'email_verified_at' => 'datetime',
//    ];

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }

    public function scopeIsAdmin($query) {
        return $query->where('role',1);
    }

    public function scopeByAddress($query, $address) {
        return $query->whereRaw( 'LOWER(`address`) LIKE ?', [ $address ] );
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
}
