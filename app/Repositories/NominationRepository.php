<?php
namespace App\Repositories;
use App\Models\Nominee;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\SendSocialMessage;
use Carbon\Carbon;
use App\Models\Circle;

class NominationRepository {

    protected $model;
    protected $userModel;
    protected $profileModel;

    public function __construct(Nominee $model, Profile $profileModel, User $userModel) {
        $this->model = $model;
        $this->profileModel = $profileModel;
        $this->userModel = $userModel;
    }

    public function getNominees($request, $circle_id) {
        return $this->model->with('nominations')->where('circle_id', $circle_id)->filter($request->all())->get();
    }

    public function addVouch($request, $circle_id) {
        $user = $request->user;
        $nominee_id = $request->nominee_id;
        $nominee = $this->model->with('nominations')->find($nominee_id);
        if($nominee->ended == 0) {
            DB::transaction(function () use($user, $nominee, $nominee_id, $circle_id) {
                $user->nominations()->syncWithoutDetaching([$nominee_id]);
                $nominee->load('nominations');
                if ($nominee->vouches_required == count($nominee->nominations)) {
                    $address = strtolower($nominee->address);
                    if (!$this->userModel->where('address', $address)->exists()) {
                        $user = $this->userModel->create(["address" => $address, "name" => $nominee->name, "circle_id" => $circle_id]);
                    }
                    if (!$this->profileModel->where('address', $address)->exists()) {
                        $this->profileModel->create(['address' => $address]);
                    }
                    $nominee->ended = 1;
                    $nominee->user_id = $user->id;
                    $nominee->save();
                    $nominee->load('user');
                }
            });
        }
        return $nominee;
    }

    public function createNominee($request) {
        $today = Carbon::today();
        $user = $request->user;
        $circle = $user->circle;
        $data = $request->only('name','address','description');
        $data = array_merge($data, ['nominated_by_user_id' => $user->id, 'circle_id' => $circle->id, 'nominated_date' => $today,
            'expiry_date' => $today->copy()->addDays($circle->nomination_days_limit), 'vouches_required' => $circle->min_vouches]);
        return $this->model->create($data);
    }

    public function checkExpiry() {
        $expired_nominees = $this->model->with('circle','nominations')->where('ended',0)->pastExpiryDate()->get();
        foreach($expired_nominees as $nominee) {
            $nominee->ended = 1;
            $nominee->save();
            $nominations = count($nominee->nominations);
            $message = "Nominee $nominee->name has only received $nominations vouch(es) and has failed";
            $nominee->circle->notify(new SendSocialMessage($message, true));
        }
    }
}
