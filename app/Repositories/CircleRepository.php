<?php

namespace App\Repositories;

use App\Models\Circle;

class CircleRepository {

    protected $model;
    public function __construct(Circle $circle) {
        $this->model = $circle;
    }

    public function getCircles($request) {
        return $this->model->filter($request->all())->with('protocol')->get();
    }

    public function createCircle($request) {
        return $this->model->create($request->only('name','protocol_id','token_name','team_sel_text','alloc_text'));
    }

    public function updateCircle($circle, $request) {
        $circle->update($request->only('name','token_name','team_sel_text','alloc_text','vouching',
            'min_vouches','nomination_days_limit','vouching_text'));

        if(!$circle->vouching) {
            $circle->nominees()->update(['ended' => 1]);
        }

        return $circle;
    }
}
