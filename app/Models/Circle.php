<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'protocol_id'
    ];

    public function protocol() {
        return $this->belongsTo('App\Models\Protocol','protocol_id','id');
    }
}
