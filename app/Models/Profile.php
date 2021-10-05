<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Profile extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['avatar', 'background', 'skills', 'bio', 'telegram_username',
        'discord_username', 'twitter_username', 'github_username',
        'medium_username', 'website', 'address', 'chat_id', 'ann_power'];

    protected $casts = ['skills' => 'array'];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'address', 'address');
    }

    public function scopeByAddress($query, $address)
    {
        return $query->where('address', strtolower($address));
    }

    public function routeNotificationForTelegram()
    {
        return $this->chat_id;
    }
}
