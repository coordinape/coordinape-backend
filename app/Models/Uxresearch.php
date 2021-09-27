<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uxresearch extends Model
{
    use HasFactory;

    protected $table = 'uxresearch';
    protected $fillable = ['circle_id', 'user_id', 'protocol_id', 'json'];

}
