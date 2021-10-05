<?php

namespace App\Repositories;

use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProfileRepository
{
    protected $model;

    public function __construct(Profile $model)
    {
        $this->model = $model;
    }

    public function getProfile($address, $relationships = [])
    {
        return $this->model->with($relationships)->byAddress($address)->first();
    }

    public function saveProfile($request)
    {
        $data = $request->only('skills', 'bio', 'telegram_username',
            'discord_username', 'twitter_username', 'github_username', 'medium_username', 'website');
        $profile = $request->profile;
        $profile->update($data);
        $profile->load(['users.circle.protocol', 'users.teammates', 'users.histories.epoch']);
        return $profile;
    }

    public function uploadProfileAvatar($request, $address)
    {
        $profile = $this->model->byAddress($address)->first();
        if ($profile) {
            $file = $request->file('file');
            $image = Image::make($file);
            $height = $image->height();
            $width = $image->width();

            if ($width > 240) {
                $height = $height * 240 / $height;
                $width = $width * 240 / $width;
            }

            $resized = $image
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->encode($file->getCLientOriginalExtension(), 80);
            $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getCLientOriginalExtension();
            $ret = Storage::put($new_file_name, $resized);
            if ($ret) {
                if ($profile->avatar && Storage::exists($profile->avatar)) {
                    Storage::delete($profile->avatar);
                }

                $profile->avatar = $new_file_name;
                $profile->save();
                return $profile;
            }
        }

        return null;
    }

    public function uploadProfileBackground($request, $address)
    {
        $profile = $this->model->byAddress($address)->first();
        if ($profile) {
            $file = $request->file('file');
            $resized = Image::make($request->file('file'))
                ->encode($file->getCLientOriginalExtension(), 80);
            $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getCLientOriginalExtension();
            $ret = Storage::put($new_file_name, $resized);
            if ($ret) {
                if ($profile->background && Storage::exists($profile->background)) {
                    Storage::delete($profile->background);
                }

                $profile->background = $new_file_name;
                $profile->save();
                return $profile;
            }
        }

        return null;
    }
}
