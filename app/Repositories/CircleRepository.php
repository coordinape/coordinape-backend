<?php

namespace App\Repositories;

use App\Helper\Utils;
use App\Models\Circle;
use App\Models\CircleMetadata;
use App\Models\Epoch;
use App\Models\Nominee;
use App\Models\PendingTokenGift;
use App\Models\Profile;
use App\Models\Protocol;
use App\Models\TokenGift;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CircleRepository
{

    protected $model;
    public function __construct(Circle $circle)
    {
        $this->model = $circle;
    }

    public function getCircles($request)
    {
        $profile = $request->user();
        if ($profile && !$profile->admin_view) {

            return $this->model->filter($request->all())->whereIn('id', $profile->circle_ids())->with('protocol')->get();
        }

        return $this->model->filter($request->all())->with('protocol')->get();
    }

    public function createCircle($request)
    {
        $data = $request->only('address', 'user_name', 'circle_name', 'protocol_id', 'protocol_name', 'uxresearch_json');

        DB::transaction(function () use ($data) {
            if (empty($data['protocol_id'])) {
                $protocol = new Protocol(['name' => $data['protocol_name']]);
                $protocol->save();
                $protocol_id = $protocol->id;
            } else {
                $protocol_id = $data['protocol_id'];
            }

            $address = strtolower($data['address']);
            $circle = $this->model->create(['name' => $data['circle_name'], 'protocol_id' => $protocol_id]);
            $user = new User([
                'name' => $data['user_name'], 'circle_id' => $circle->id,
                'role' => config('enums.user_types.admin'), 'address' => $address
            ]);
            $user->save();
            self::addCoordinapeUserToCircle($circle->id);
            Profile::firstOrCreate([
                'address' => $address
            ]);

            if (!empty($data['uxresearch_json'])) {
                $research = new CircleMetadata(['circle_id' => $circle->id, 'json' => $data['uxresearch_json']]);
                $research->save();
            }
            return $circle;
        });
    }

    public function updateCircle($circle, $request)
    {
        $circle->update($request->only(
            'name',
            'token_name',
            'team_sel_text',
            'alloc_text',
            'vouching',
            'min_vouches',
            'nomination_days_limit',
            'vouching_text',
            'team_selection',
            'default_opt_in',
            'discord_webhook',
            'only_giver_vouch'
        ));

        if (!$circle->vouching) {
            $circle->nominees()->update(['ended' => 1]);
        }

        return $circle;
    }

    public function uploadCircleLogo($request)
    {
        $file = $request->file('file');
        $extension = strtolower($file->getCLientOriginalExtension()) == 'jfif' ? 'jpeg' : strtolower($file->getCLientOriginalExtension());
        $resized = Image::make($file)
            ->resize(100, null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode($extension, 80);
        $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
        $ret = Storage::put($new_file_name, $resized);
        if ($ret) {
            $circle = $request->user->circle;
            if ($circle->logo && Storage::exists($circle->logo)) {
                Storage::delete($circle->logo);
            }
            $circle->logo = $new_file_name;
            $circle->save();
            return $circle;
        }

        return null;
    }

    public function getWebhook($circle_id)
    {
        $circle = $this->model->find($circle_id);
        return $circle->discord_webhook ?: '';
    }

    public function fullCircleData($request, $circle_id)
    {
        $profile = $request->user();
        $user = $profile->users()->where('circle_id', $circle_id)->first();
        if ($profile->admin_view || $user) {
            $nominees = Nominee::where('circle_id', $circle_id)->get();
            $users = User::where('circle_id', $circle_id)->get();
            $latestEpoch = Epoch::where('circle_id', $circle_id)->whereNotNull('number')->orderBy('number', 'desc')->first();
            $latestEpochId = $latestEpoch ? $latestEpoch->id : null;
            $token_gifts = [];
            $pending_gifts = [];
            if ($latestEpoch) {
                if ($latestEpoch->ended == 1) {
                    $token_gifts = Utils::queryCache($request, function () use ($circle_id, $user, $latestEpochId) {
                        $query = TokenGift::fromCircle($circle_id)->where(function ($q) use ($user) {
                            if ($user) {
                                $q->where('sender_id', '<>', $user->id)->orWhere('recipient_id', '<>', $user->id);
                            }
                        })->where('epoch_id', $latestEpochId);
                        $givesWithoutUser = $query->selectWithoutNote()->get();
                        if ($user) {
                            $queryUserGives = TokenGift::fromCircle($circle_id)->where(function ($q) use ($user) {
                                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                            });
                            $givesWithoutUser = $givesWithoutUser->merge($queryUserGives->selectWithNoteAddress()->get());
                        }
                        return $givesWithoutUser;
                    }, 60, $circle_id);
                }
                $query = PendingTokenGift::fromCircle($circle_id)->where(function ($q) use ($user) {
                    if ($user) {
                        $q->where('sender_id', '<>', $user->id)->orWhere('recipient_id', '<>', $user->id);
                    }
                });
                $pending_gifts = $query->selectWithoutNote()->get();
                if ($user) {
                    $queryUserGives = PendingTokenGift::fromCircle($circle_id)->where(function ($q) use ($user) {
                        $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                    });
                    $pending_gifts = $pending_gifts->merge($queryUserGives->selectWithNoteAddress()->get());
                }
            }
            return compact('nominees', 'users', 'token_gifts', 'pending_gifts');
        }
        return null;
    }

    public function addCoordinapeUserToCircle($circle_id)
    {
        $profile = Profile::firstOrCreate(['address' => config('ape.coordinape_user_address')]);
        $coordinape_user = new User([
            'address' => config('ape.coordinape_user_address'),
            'name' => 'Coordinape',
            'role' => config('enums.user_types.coordinape'),
            'circle_id' => $circle_id,
            'non_receiver' => 0,
            'fixed_non_receiver' => 0,
            'starting_tokens' => 0,
            'non_giver' => 1,
            'give_token_remaining' => 0,
            'bio' => "Coordinape is that the platform you’re using right now! We currently offer our service for free and invite people to allocate to us from within your circles. All funds received go towards funding the team and our operations."
        ]);
        $coordinape_user->save();
    }
}
