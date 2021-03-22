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

class DataController extends Controller
{
    protected $repo ;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getCircles(Request $request): JsonResponse
    {
        return response()->json(Circle::all());
    }

    public function createCircle(CircleRequest $request)
    {
        $circle = new Circle($request->all());
        $circle->save();
        return response()->json($circle);
    }

    public function updateCircle(Circle $circle, CircleRequest $request): JsonResponse
    {
        $circle->update($request->all());
        return response()->json($circle);
    }

    public function getUser($address): JsonResponse {
        $user = User::byAddress($address)->first();
        $user->load(['teammates','pendingSentGifts','sentGifts']);

        return response()->json($user);
    }

    public function getUsers(Request $request): JsonResponse {
        return response()->json(User::filter($request->all())->get());
    }

    public function createUser(UserRequest $request): JsonResponse {
        $data = $request->all();
        $data['address'] =  strtolower($data['address']);
        $user = new User($data);
        $user->save();
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
        $user->update($data);
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
        $token_used = 0;
        $toKeep = [];
        foreach($gifts as $gift) {
            $recipient_address = strtolower($gift['recipient_address']);
            if($users->has($recipient_address))
            {
                if($user->id==$users[$recipient_address]->id)
                    continue;

                $gift['sender_id'] = $user->id;
                $gift['sender_address'] = strtolower($address);
                $gift['recipient_address'] = $recipient_address;
                $gift['recipient_id'] = $users[$recipient_address]->id;

                $token_used+= $gift['tokens'];
                $pendingGift = $user->pendingSentGifts()->where('recipient_id',$gift['recipient_id'])->first();

                if($pendingGift)
                {
                    if($gift['tokens']==0 && $gift['note']=='' )
                    {
                        $pendingGift->delete();

                    }
                    else
                    {
                        $pendingGift->tokens = $gift['tokens'];
                        $pendingGift->note = $gift['note'];
                        $pendingGift->save();
                    }
                }
                else
                {
                    if($gift['tokens']==0 && $gift['note']=='' )
                        continue;

                    $pendingGift = $user->pendingSentGifts()->create($gift);
                }

                $toKeep[] = $pendingGift->recipient_id;
                $users[$recipient_address]->give_token_received = $users[$recipient_address]->pendingReceivedGifts()->get()->SUM('tokens');
                $users[$recipient_address]->save();
            }
        }

        $this->repo->resetGifts($user,$toKeep);
        $user->load(['teammates','pendingSentGifts','sentGifts']);
        return response()->json($user);
    }

    public function getPendingGifts(Request $request): JsonResponse {

        return response()->json(PendingTokenGift::filter($request->all())->get());
    }

    public function getGifts(Request $request): JsonResponse {
        return response()->json(TokenGift::filter($request->all())->get());
    }

    public function updateTeammates(TeammatesRequest $request) : JsonResponse {
        $address = $request->address;
        $user = User::byAddress($address)->first();
        $teammates = $request->teammates;
        $this->repo->resetGifts($user,$teammates);
        if($teammates) {
            $user->teammates()->sync($teammates);
        }
        $user->load(['teammates','pendingSentGifts','sentGifts']);
        return response()->json($user);
    }

    public function generateCsv(CsvRequest $request) {
        return $this->repo->getEpochCsv($request->epoch, $request->circle_id);
    }
}
