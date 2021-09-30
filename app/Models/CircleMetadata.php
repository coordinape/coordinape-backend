<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleMetadata extends Model
{
    use HasFactory;

    protected $table = 'circle_metadata';
    protected $fillable = ['circle_id', 'json'];

}
