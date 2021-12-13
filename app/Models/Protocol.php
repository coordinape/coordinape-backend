<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Protocol extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['name','is_verified'];

    public function routeNotificationForTelegram()
    {
        if(config('app.env') != 'production')
            return '-573708082';

        return $this->telegram_id;
    }

    public function circles() {
        return $this->hasMany('App\Models\Circle','protocol_id');
    }
}
