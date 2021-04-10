<?php

namespace App\Http\Controllers;

use App\Models\Circle;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\PendingTokenGift;
use App\Http\Requests\CircleRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\GiftRequest;
use DB;
use App\Models\TokenGift;
use App\Repositories\EpochRepository;
use App\Http\Requests\CsvRequest;
use App\Http\Requests\TeammatesRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Http\Requests\FileUploadRequest;
use App\Helper\Utils;
use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\AdminUserRequest;
use App\Models\Epoch;
use Carbon\Carbon;


class DataController extends Controller
{
    protected $repo ;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getCircles(Request $request, $subdomain = null): JsonResponse
    {
        return response()->json(Circle::all());
    }

    public function createCircle(CircleRequest $request)
    {
        $circle = new Circle($request->all());
        $circle->save();
        return response()->json($circle);
    }

    public function updateCircle(Circle $circle, CircleRequest $request, $subdomain=null): JsonResponse
    {
        $circle->update($request->all());
        return response()->json($circle);
    }

    public function getUser($address): JsonResponse {
        $user = User::byAddress($address)->first();
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function getUser2($subdomain, $address): JsonResponse {
        $circle_id = Utils::getCircleIdByName($subdomain);
        $user = User::byAddress($address);
        if($subdomain)
            $user->where('circle_id',$circle_id);
        $user = $user->first();
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function getUsers(Request $request, $subdomain = null): JsonResponse {
        $circle_id = Utils::getCircleIdByName($subdomain);
        $data = $request->all();
        $data['is_hidden'] = 0;
        $users = User::filter($data);
        if($subdomain)
            $users->where('circle_id',$circle_id);

        $users = $users->get();
        return response()->json($users);
    }

    public function createUser(AdminCreateUserRequest $request, $subdomain): JsonResponse {
        $data = $request->all();
        $data['address'] =  strtolower($data['address']);
        $user = new User($data);
        $user->save();
        return response()->json($user);
    }

    public function updateUser2(UserRequest $request, $subdomain, $address): JsonResponse
    {
        $user = $request->user;
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $data = $request->all();
        $data = $data['data'];
        $data['address'] =  strtolower($data['address']);
        $user = $this->repo->removeAllPendingGiftsReceived($user, $data);
        return response()->json($user);
    }

    public function adminUpdateUser(AdminUserRequest $request, $subdomain, $address): JsonResponse
    {
        $user = $request->user;
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $data = $request->all();
        $data = $data['data'];
        $data['address'] =  strtolower($data['address']);
        $user = $this->repo->removeAllPendingGiftsReceived($user, $data);
        return response()->json($user);
    }


    public function updateUser($address, UserRequest $request): JsonResponse
    {
        $user = $request->user;
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $data = $request->all();
        $data = $data['data'];
        $data['address'] =  strtolower($data['address']);
        $user = $this->repo->removeAllPendingGiftsReceived($user, $data);
        return response()->json($user);
    }

    public function updateGifts2(GiftRequest $request, $subdomain, $address): JsonResponse
    {
        $user = $request->user;
        $gifts = $request->gifts;
        $addresses = [];

        foreach($gifts as $gift) {
            $addresses[] = strtolower($gift['recipient_address']);
        }

        $users = User::where('circle_id',$request->circle_id)->whereIn(DB::raw('lower(address)'),$addresses)->get()->keyBy('address');

        DB::transaction(function () use ($users, $user, $gifts, $address) {
            $token_used = 0;
            $toKeep = [];
            foreach ($gifts as $gift) {
                $recipient_address = strtolower($gift['recipient_address']);
                if ($users->has($recipient_address)) {
                    if ($user->id == $users[$recipient_address]->id)
                        continue;

                    $gift['sender_id'] = $user->id;
                    $gift['sender_address'] = strtolower($address);
                    $gift['recipient_address'] = $recipient_address;
                    $gift['recipient_id'] = $users[$recipient_address]->id;

                    $token_used += $gift['tokens'];
                    $pendingGift = $user->pendingSentGifts()->where('recipient_id', $gift['recipient_id'])->first();

                    if ($pendingGift) {
                        if ($gift['tokens'] == 0 && $gift['note'] == '') {
                            $pendingGift->delete();

                        } else {
                            $pendingGift->tokens = $gift['tokens'];
                            $pendingGift->note = $gift['note'];
                            $pendingGift->save();
                        }
                    } else {
                        if ($gift['tokens'] == 0 && $gift['note'] == '')
                            continue;

                        $pendingGift = $user->pendingSentGifts()->create($gift);
                    }

                    $toKeep[] = $pendingGift->recipient_id;
                    $users[$recipient_address]->give_token_received = $users[$recipient_address]->pendingReceivedGifts()->get()->SUM('tokens');
                    $users[$recipient_address]->save();
                }
            }

            $this->repo->resetGifts($user, $toKeep);
        });

        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function updateGifts($address, GiftRequest $request): JsonResponse
    {
        $user = $request->user;
        $gifts = $request->gifts;
        $addresses = [];

        foreach($gifts as $gift) {
            $addresses[] = strtolower($gift['recipient_address']);
        }
        $users = User::whereIn(DB::raw('lower(address)'),$addresses)->get()->keyBy('address');

        DB::transaction(function () use ($users, $user, $gifts, $address) {
            $token_used = 0;
            $toKeep = [];
            foreach ($gifts as $gift) {
                $recipient_address = strtolower($gift['recipient_address']);
                if ($users->has($recipient_address)) {
                    if ($user->id == $users[$recipient_address]->id)
                        continue;

                    $gift['sender_id'] = $user->id;
                    $gift['sender_address'] = strtolower($address);
                    $gift['recipient_address'] = $recipient_address;
                    $gift['recipient_id'] = $users[$recipient_address]->id;

                    $token_used += $gift['tokens'];
                    $pendingGift = $user->pendingSentGifts()->where('recipient_id', $gift['recipient_id'])->first();

                    if ($pendingGift) {
                        if ($gift['tokens'] == 0 && $gift['note'] == '') {
                            $pendingGift->delete();

                        } else {
                            $pendingGift->tokens = $gift['tokens'];
                            $pendingGift->note = $gift['note'];
                            $pendingGift->save();
                        }
                    } else {
                        if ($gift['tokens'] == 0 && $gift['note'] == '')
                            continue;

                        $pendingGift = $user->pendingSentGifts()->create($gift);
                    }

                    $toKeep[] = $pendingGift->recipient_id;
                    $users[$recipient_address]->give_token_received = $users[$recipient_address]->pendingReceivedGifts()->get()->SUM('tokens');
                    $users[$recipient_address]->save();
                }
            }

            $this->repo->resetGifts($user, $toKeep);
        });

        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function getPendingGifts(Request $request, $subdomain = null): JsonResponse {
        $filters = $request->all();
        if($subdomain) {
            $circle_id = Utils::getCircleIdByName($subdomain);
            if($circle_id) {
                $filters['circle_id'] = $circle_id;
            }
            else {
               return response()->json([]);
            }
        }

        return response()->json(PendingTokenGift::filter($filters)->get());
    }

    public function getGifts(Request $request, $subdomain = null): JsonResponse {
        $filters = $request->all();
        if($subdomain) {
            $circle_id = Utils::getCircleIdByName($subdomain);
            if($circle_id) {
                $filters['circle_id'] = $circle_id;
            }
            else {
                return response()->json([]);
            }
        }
        return response()->json(TokenGift::filter($filters)->get());
    }

    public function updateTeammates(TeammatesRequest $request, $subdomain=null) : JsonResponse {
        $user = $request->user;
        $teammates = $request->teammates;
        DB::transaction(function () use ($teammates, $user) {
            $this->repo->resetGifts($user, $teammates);
            if ($teammates) {
                $user->teammates()->sync($teammates);
            }
        });
        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function generateCsv(CsvRequest $request, $subdomain = null)
    {
        $circle_id = Utils::getCircleIdByName($subdomain);
        if (!$circle_id) {
            if (!$request->circle_id)
                return response()->json(['error' => 'Circle not Found'], 422);
            $circle_id = $request->circle_id;
        }
        return $this->repo->getEpochCsv($request->epoch, $circle_id, $request->grant);
    }

    public function uploadAvatar(FileUploadRequest $request, $subdomain=null) : JsonResponse {

        $file = $request->file('file');
        $resized = Image::make($request->file('file'))
            ->resize(100, null, function ($constraint) { $constraint->aspectRatio(); } )
            ->encode($file->getCLientOriginalExtension(),80);
        $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)).'_'.time().'.'.$file->getCLientOriginalExtension();
        $ret = Storage::put($new_file_name, $resized);
        if($ret) {
            $user = User::byAddress($request->get('address'))->where('circle_id',$request->circle_id)->first();
            if($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }

            $user->avatar = $new_file_name;
            $user->save();
            return response()->json($user);
        }

        return response()->json(['error' => 'File Upload Failed' ,422]);
//        dd(Storage::disk('s3')->allFiles(''));
    }

    public function epoches(Request $request, $subdomain) {
        $circle_id = Utils::getCircleIdByName($subdomain);
        if (!$circle_id) {
            return response()->json(['error' => 'Circle not Found'], 422);
        }
        $epoches = Epoch::where('circle_id', $circle_id);
        if($request->current) {
            $today = Carbon::today()->toDateString();
            $epoches->whereDate('start_date', '<=', $today)->whereDate('end_date','>=', $today);
        }
        $epoches = $epoches->get();
        return response()->json($epoches);
    }
}
