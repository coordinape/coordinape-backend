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

class DataController extends Controller
{

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
        return response()->json($user);
    }

    public function getUsers(Request $request): JsonResponse {
        return response()->json(User::filter($request->all())->get());
    }

    public function createUser(UserRequest $request): JsonResponse {

        $user = new User($request->all());
        $user->save();
        return response()->json($user);
    }

    public function updateUser($address, UserRequest $request): JsonResponse
    {
        $user = User::byAddress($address)->first();
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $user->update($request->all());
        return response()->json($user);
    }

    public function updateGifts($address, GiftRequest $request): JsonResponse
    {
        $user = User::byAddress($address)->first();
        if(!$user)
            return response()->json(['error'=> 'Address not found'],422);

        $gifts = $request->get('gifts');
        $addresses = [];
        foreach($gifts as $gift) {
            $addresses[] = $gift['recipient_address'];
        }
        $users = User::whereIn(DB::raw('lower(address)'),$addresses)->get()->keyBy('address');
        $toInsert = [];
        $token_used = 0;
        $toKeep = [];
        foreach($gifts as $gift) {
            if($users->has($gift['recipient_address']))
            {
                if($user->id==$users[$gift['recipient_address']]->id)
                    continue;

                $gift['sender_id'] = $user->id;
                $gift['sender_address'] = strtolower($address);
                $gift['circle_id'] = $request->get('circle_id');
                $gift['recipient_address'] = strtolower($gift['recipient_address']);
                $gift['recipient_id'] = $users[$gift['recipient_address']]->id;

                $toInsert[] = $gift;
                $token_used+= $gift['tokens'];
                $pendingGift = $user->pendingSentGifts()->where('recipient_id',$gift['recipient_id'])->first();

                if($pendingGift)
                {
                    if($gift['tokens']==0)
                        $pendingGift->delete();

                    $pendingGift->tokens = $gift['tokens'];
                    $pendingGift->save();
                }
                else
                {
                    $pendingGift = $user->pendingSentGifts()->create($gift);
                }

                $toKeep[] = $pendingGift->id;
                $users[$gift['recipient_address']]->give_token_received = $users[$gift['recipient_address']]->pendingReceivedGifts()->get()->SUM('tokens');
                $users[$gift['recipient_address']]->save();
            }
        }

        $existingGifts = $user->pendingSentGifts()->whereNotIn('id',$toKeep)->get();
        foreach($existingGifts as $existingGift) {
            $rUser = $existingGift->recipient;
            $existingGift->delete();
            $rUser->give_token_received = $rUser->pendingReceivedGifts()->get()->SUM('tokens');
            $rUser->save();
        }

        $user->give_token_remaining = 100-$token_used;
        $user->save();

        return response()->json($user);
    }

    public function getPendingGifts(Request $request): JsonResponse {

        return response()->json(PendingTokenGift::filter($request->all())->get());
    }

    public function getGifts(Request $request): JsonResponse {
        return response()->json(TokenGift::filter($request->all())->get());
    }
}
