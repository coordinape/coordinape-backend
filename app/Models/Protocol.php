<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Protocol extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['name'];

    public function routeNotificationForTelegram()
    {
        if(env('APP_DOMAIN') != 'coordinape.me')
            return '-573708082';

        return $this->telegram_id;
    }
}
