<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenGift extends Model
{
    use HasFactory;

    protected $searchable = [
        'recipient_id',
        'sender_id',
        'circle_id',
        'id',
        'epoch_id'
    ];
    protected $fillable = [
        'sender_address',
        'recipient_address',
        'recipient_id',
        'sender_id',
        'tokens',
        'circle_id',
        'note',
        'dts_created',
        'epoch_id'
    ];

    public function scopeFilter($query, $filters)
    {
        foreach ($filters as $key => $filter) {
            if (in_array($key, $this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }

    public function scopeSelectWithNoteAddress($query)
    {
        return $query->select(['id', 'note','recipient_address', 'sender_address', 'recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'dts_created']);
    }

    public function scopeSelectWithoutNote($query)
    {
        return $query->select(['id', 'recipient_address', 'sender_address', 'recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'dts_created']);
    }

    public function scopeSelectWithoutAddressNote($query)
    {
        return $query->select(['id', 'recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'dts_created']);
    }

    public function scopeSelectWithNoteNoAddress($query)
    {
        return $query->select(['id', 'note','recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'dts_created']);
    }

    public function scopeFromCircle($query, $circle_id)
    {
        return $query->where('circle_id', $circle_id);
    }

    public function scopeFromEpochId($query, $epoch_id)
    {
        return $query->where('epoch_id', $epoch_id);
    }

    public function recipient()
    {
        return $this->belongsTo('App\Models\User', 'recipient_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo('App\Models\User', 'sender_id', 'id');
    }
}
