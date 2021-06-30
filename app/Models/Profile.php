<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['avatar','background','skills','bio','telegram_username',
                        'discord_username','twitter_username','github_username','medium_username','website','address'];

    protected $casts = ['skills' => 'array'];

    public function users() {
        return $this->hasMany('App\Models\User','address','address');
    }

    public function scopeByAddress($query,$address) {
        return $query->where('address',strtolower($address));
    }
}
