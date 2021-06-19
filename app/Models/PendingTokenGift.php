<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingTokenGift extends Model
{
    use HasFactory;

    protected $searchable = [
//        'sender_address',
//        'recipient_address',
        'circle_id',
        'recipient_id',
        'sender_id',
        'id'
    ];
    protected $fillable = [

        'sender_address',
        'recipient_address',
        'recipient_id',
        'note',
        'sender_id',
        'tokens',
        'circle_id',
    ];

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }

    public function recipient() {
        return $this->belongsTo('App\Models\User','recipient_id','id');
    }

    public function sender() {
        return $this->belongsTo('App\Models\User','sender_id','id');
    }

    public function scopeSentToday($query) {
        return $query->whereDate('created_at',Carbon::today());
    }
}
