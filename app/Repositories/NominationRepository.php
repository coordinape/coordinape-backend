<?php
namespace App\Repositories;
use App\Models\Nominee;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\SendSocialMessage;
use Carbon\Carbon;
use DB;
use App\Helper\Utils;

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
        return $this->model->with('nominations','nominator')->where('circle_id', $circle_id)->filter($request->all())->get();
    }

    private function hasEnoughVouches($nominee, $circle) {
        $has_enough_vouches = 0;
        if ($circle->calculate_vouching_percent == 1) {
            $circleUsersCount = $circle->users()->count();
            $nominations_required = $circleUsersCount * $circle->min_vouches_percent / 100;

            // nomination apparently is 1 vouch
            $has_enough_vouches = count($nominee->nominations + 1) >= $nominations_required;
        } else {
            // nomination apparently is 1 vouch
            $has_enough_vouches = count($nominee->nominations + 1) >= ($nominee->vouches_required);
        }

        return $has_enough_vouches;
    }

    public function addVouch($request, $circle_id) {
        $user = $request->user;
        $nominee_id = $request->nominee_id;
        $nominee = $this->model->with('nominations','nominator')->where('circle_id',$user->circle_id)->find($nominee_id);
        if(!$nominee) {
            return ['message' => 'Nominee not found'];
        }
        if($nominee->ended == 0) {
            DB::transaction(function () use($user, $nominee, $nominee_id, $circle_id) {
                $user->nominations()->syncWithoutDetaching([$nominee_id]);
                $nominee->load('nominations');
                $circle = $user->circle;
                $nominee_name = Utils::cleanStr($nominee->name);
                $voucher_name = Utils::cleanStr($user->name);
                $circle->notify(new SendSocialMessage("$nominee_name has been vouched for by $voucher_name!", false));

                if ($this->hasEnoughVouches()) {
                    $address = strtolower($nominee->address);
                    $added_user = $this->userModel->where('address', $address)->where('circle_id',$circle_id)->first();
                    if (!$added_user) {
                        $added_user = $this->userModel->create(["address" => $address, "name" => $nominee->name, "circle_id" => $circle_id]);
                    }
                    if (!$this->profileModel->where('address', $address)->exists()) {
                        $this->profileModel->create(['address' => $address]);
                    }
                    $nominee->ended = 1;
                    $nominee->user_id = $added_user->id;
                    $nominee->save();
                    $nominee->load('user');
                    $circle->notify(new SendSocialMessage(
                        "$nominee_name has received enough vouches and is now in the circle!", false));

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
        $nominee = $this->model->create($data);
        $nominee->load('nominations','nominator');
        $nominee_name = Utils::cleanStr($nominee->name);
        $voucher_name = Utils::cleanStr($user->name);
        $circle->notify(new SendSocialMessage(
            "$nominee_name has been nominated by $voucher_name! You can vouch for them at https://app.coordinape.com/vouching", false));

        if ($this->hasEnoughVouches()) {
            $address = $nominee->address;
            $createdUser = $this->userModel->create(["address" => $address, "name" => $nominee->name, "circle_id" => $circle->id]);
            if (!$this->profileModel->where('address', $address)->exists()) {
                $this->profileModel->create(['address' => $address]);
            }
            $nominee->ended = 1;
            $nominee->user_id = $createdUser->id;
            $nominee->save();
            $circle->notify(new SendSocialMessage(
                "$nominee_name has received enough vouches and is now in the circle!", false));
        }

        return $nominee;
    }

    public function checkExpiry() {
        $expired_nominees = $this->model->with('circle','nominations')->where('ended',0)->pastExpiryDate()->get();
        foreach($expired_nominees as $nominee) {
            $nominee->ended = 1;
            $nominee->save();
            $nominations = count($nominee->nominations);
            $nominee_name = Utils::cleanStr($nominee->name);
            $message = "Nominee $nominee_name has only received $nominations vouch(es) and has failed";
            $nominee->circle->notify(new SendSocialMessage($message, true));
        }
    }
}
