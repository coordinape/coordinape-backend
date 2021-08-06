<?php

namespace App\Repositories;

use App\Models\Circle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CircleRepository {

    protected $model;
    public function __construct(Circle $circle) {
        $this->model = $circle;
    }

    public function getCircles($request) {
        return $this->model->filter($request->all())->with('protocol')->get();
    }

    public function createCircle($request) {
        return $this->model->create($request->only('name','token_name','team_sel_text','alloc_text','vouching',
            'min_vouches','nomination_days_limit','vouching_text','team_selection','default_opt_in'));
    }

    public function updateCircle($circle, $request) {
        $circle->update($request->only('name','token_name','team_sel_text','alloc_text','vouching',
            'min_vouches','nomination_days_limit','vouching_text','team_selection','default_opt_in'));

        if(!$circle->vouching) {
            $circle->nominees()->update(['ended' => 1]);
        }

        return $circle;
    }

    public function uploadCircleLogo($request) {
        $file = $request->file('file');
        $extension = strtolower($file->getCLientOriginalExtension()) == 'jfif' ? 'jpeg' : strtolower($file->getCLientOriginalExtension());
        $resized = Image::make($file)
            ->resize(100, null, function ($constraint) { $constraint->aspectRatio(); } )
            ->encode($extension,80);
        $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)).'_'.time().'.'.$extension;
        $ret = Storage::put($new_file_name, $resized);
        if($ret) {
            $circle = $request->user->circle;
            if($circle->logo && Storage::exists($circle->logo)) {
                Storage::delete($circle->logo);
            }
            $circle->logo = $new_file_name;
            $circle->save();
            return $circle;
        }

        return null;
    }

    public function getWebhook($circle_id) {
        $circle = $this->model->find($circle_id);
       return $circle->discord_webhook ?:'';
    }

    public function updateWebhook($request, $circle_id) {
        $circle = $this->model->find($circle_id);
        $circle->discord_webhook = $request->discord_webhook;
        $circle->save();
        return $circle->discord_webhook;
    }
}
