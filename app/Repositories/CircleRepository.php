<?php

namespace App\Repositories;

use App\Models\Circle;
use App\Models\CircleMetadata;
use App\Models\Profile;
use App\Models\Protocol;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CircleRepository {

    protected $model;
    public function __construct(Circle $circle) {
        $this->model = $circle;
    }

    public function getCircles($request) {
        $profile = $request->user();
        if($profile) {

            if($profile->admin_view)
                return $this->model->all();

            return $this->model->whereIn('id', $profile->currentAccessToken()->abilities)->with('protocol')->get();
        }

        return $this->model->filter($request->all())->with('protocol')->get();
    }

    public function createCircle($request) {
        $data = $request->only('address', 'user_name', 'circle_name', 'protocol_id', 'protocol_name', 'uxresearch_json');
        if(empty($data['protocol_id'])) {
            $protocol = new Protocol(['name' => $data['protocol_name']]);
            $protocol->save();
            $protocol_id = $protocol->id;
        } else {
            $protocol_id = $data['protocol_id'];
        }

        $address = strtolower($data['address']);
        $circle = $this->model->create(['name' => $data['circle_name'], 'protocol_id' => $protocol_id]);
        $user = new User(['name' => $data['user_name'], 'circle_id' => $circle->id,
            'role' => config('enums.user_types.admin'), 'address' => $address]);
        $user->save();
        Profile::firstOrCreate([
            'address' => $address
        ]);

        if (!empty($data['uxresearch_json'])) {
            $research = new CircleMetadata(['circle_id' => $circle->id, 'json' => $data['uxresearch_json']]);
            $research->save();
        }
        return $circle;
    }

    public function updateCircle($circle, $request) {
        $circle->update($request->only('name','token_name','team_sel_text','alloc_text','vouching',
            'min_vouches','nomination_days_limit','vouching_text','team_selection','default_opt_in',
            'discord_webhook','only_giver_vouch'));

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

    public function fullCircle($request, $circle_id) {

        $profile = $request->user();
        $user = $profile->users()->where('circle_id', $circle_id)->first();
        if ($user) {
            $circle = $user->circle;
            $users = $circle->users;
            $nominees = $circle->nominees;
            $latestEpoch = $circle->epoches()->where('epoches.ended', 1)->whereNotNull('epoches.number')->orderBy('epoches.number', 'desc')->first();
            $latestEpochId = $latestEpoch ? $latestEpoch->id : null;
            $users->load(['pendingSentGifts' => function ($q) {
                $q->selectWithoutAddressNote();
            },
                'pendingReceivedGifts' => function ($q) {
                    $q->selectWithoutAddressNote();
                }, 'receivedGifts' => function ($q) use ($latestEpochId) {
                    $q->where('epoch_id', $latestEpochId)->selectWithoutAddressNote();
                }, 'sentGifts' => function ($q) use ($latestEpochId) {
                    $q->where('epoch_id', $latestEpochId)->selectWithoutAddressNote();
                }
            ]);

            $user->load(['pendingSentGifts' => function ($q) {
                $q->selectWithNoteNoAddress();
            },
                'pendingReceivedGifts' => function ($q) {
                    $q->selectWithNoteNoAddress();
                }, 'receivedGifts' => function ($q) use ($latestEpochId) {
                    $q->where('epoch_id', $latestEpochId)->selectWithNoteNoAddress();
                }, 'sentGifts' => function ($q) use ($latestEpochId) {
                    $q->where('epoch_id', $latestEpochId)->selectWithNoteNoAddress();
                }
            ]);
        }
        return $user;
    }
}
