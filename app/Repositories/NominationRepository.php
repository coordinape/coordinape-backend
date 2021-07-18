<?php
namespace App\Repositories;
use App\Models\Nominee;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;

class NominationRepository {

    public function __construct(Nominee $model) {
        $this->model = $model;
    }

    public function getNominees($request, $circle_id) {
        return $this->model->where('circle_id', $circle_id)->filter($request->all())->get();
    }

    public function addVouch($request, $circle_id) {
        $user = $request->user;
        $nominee_id = $request->nominee_id;
        $nominee = Nominee::with('nominations')->find($nominee_id);
        if($nominee->ended == 0) {
            $user->nominations()->syncWithoutDetaching([$nominee_id]);
            $nominee->load('nominations');
            if($nominee->vouches_required == count($nominee->nominations)) {
                $address = strtolower($nominee->address);
                if(!User::where('address' , $address)->exists()) {
                    $user = new User(["address" => $address, "name" => $nominee->name, "circle_id" => $circle_id]);
                    $user->save();
                }
                if(!Profile::where('address' ,$address)->exists()) {
                    $profile = new Profile(['address' => $address]);
                    $profile->save();
                }
                $nominee->ended = 1;
                $nominee->user_id = $user->id;
                $nominee->save();
                $nominee->load('user');
            }
        }
        return $nominee;
    }

    public function createNominee($request) {
        $today = Carbon::today();
        $circle = $request->circle;
        $user = $request->user;
        $data = $request->only('name','address','description');
        $data = array_merge($data, ['nominated_by_user_id' => $user->id, 'circle_id' => $circle->id, 'nominated_date' => $today,
            'expiry_date' => $today->copy()->addDays($circle->nomination_days_limit), 'vouches_required' => $circle->min_vouches]);
        $nominee = new Nominee($data);
        $nominee->save();
        return $nominee;
    }
}
