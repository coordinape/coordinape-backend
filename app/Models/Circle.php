<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Circle extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'protocol_id',
        'token_name',
        'team_sel_text',
        'alloc_text'
    ];
    protected $searchable = [
        'protocol_id',
    ];

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }
    public function protocol() {
        return $this->belongsTo('App\Models\Protocol','protocol_id','id');
    }

    public function users() {
        return $this->hasMany('App\Models\User', 'circle_id', 'id');
    }
}
