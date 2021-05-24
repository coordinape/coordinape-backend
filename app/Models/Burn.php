<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Burn extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','epoch_id','circle_id','tokens_burnt','regift_percent','original_amount'];
    protected $searchable = [
        'circle_id',
        'epoch_id',
        'user_id'
    ];

    public function scopeFilter($query, $filters) {
        foreach($filters as $key=>$filter) {
            if(in_array($key,$this->searchable)) {
                $query->where($key, $filter);
            }
        }
        return $query;
    }
}
