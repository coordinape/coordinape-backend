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
}
