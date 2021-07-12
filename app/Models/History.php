<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $fillable = ['bio','epoch_id', 'circle_id'];

    public function circle() {
        return $this->belongsTo('App\Models\Circle','circle_id','id');
    }

    public function epoch() {
        return $this->belongsTo('App\Models\Epoch','epoch_id','id');
    }
}
