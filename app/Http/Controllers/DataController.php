<?php

namespace App\Http\Controllers;

use App\Helper\Utils;
use App\Http\Requests\CsvRequest;
use App\Http\Requests\NewGiftRequest;
use App\Http\Requests\TeammatesRequest;
use App\Models\Epoch;
use App\Models\Protocol;
use App\Models\User;
use App\Repositories\EpochRepository;
use App\Repositories\GiftRepository;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class DataController extends Controller
{
    protected $repo, $giftRepo;

    public function __construct(EpochRepository $repo, GiftRepository $giftRepo)
    {
        $this->repo = $repo;
        $this->giftRepo = $giftRepo;
    }

    public function getProtocols(Request $request): JsonResponse
    {
        return response()->json(Protocol::all());
    }

    public function newUpdateGifts(NewGiftRequest $request, $circle_id, $address): JsonResponse
    {
        $user = $request->user;
        $this->repo->newUpdateGifts($request, $user->address, $circle_id);
        $user->load(['teammates', 'pendingSentGifts']);
        return response()->json($user);
    }

    public function getPendingGifts(Request $request, $circle_id = null): JsonResponse
    {
        return response()->json($this->giftRepo->getPendingGifts($request, $circle_id));
    }

    public function newGetGifts(Request $request)
    {
        $data = $request->all();
        if (!empty($data['circle_id'])) {
            $gifts = $this->giftRepo->newGetGifts($request, $data['circle_id']);
            if ($gifts) {
                return response()->json($gifts);
            }
        }
        return response()->json(['message' => 'Please provide a circle id that the user is part of'], 403);

    }

    public function getGifts(Request $request, $circle_id = null): JsonResponse
    {
        return response()->json($this->giftRepo->getGifts($request, $circle_id));
    }

    public function updateTeammates(TeammatesRequest $request, $circle_id): JsonResponse
    {

        $user = $request->user;
        $teammates = $request->teammates;
        $circle_teammates = User::where('circle_id', $circle_id)->where('id', '<>', $user->id)->whereIn('id', $teammates)->pluck('id');
        DB::transaction(function () use ($circle_teammates, $user) {
            $this->repo->resetGifts($user, $circle_teammates);
            if ($circle_teammates) {
                $user->teammates()->sync($circle_teammates);
            }
        });
        $user->load(['teammates', 'pendingSentGifts']);
        return response()->json($user);
    }

    public function generateCsv(CsvRequest $request, $circle_id = null)
    {
        if (!$circle_id) {
            if (!$request->circle_id)
                return response()->json(['message' => 'Circle not Found'], 422);
            $circle_id = $request->circle_id;
        }

        if (!Utils::checkTokenPermission($request, $circle_id)) {
            return response()->json(['message' => 'User has no permission to view this circle'], 403);
        }
        $epoch = null;
        if ($request->epoch_id) {
            $epoch = Epoch::with('circle.protocol')->where('circle_id', $circle_id)->where('id', $request->epoch_id)->first();

        } else if ($request->epoch) {
            $epoch = Epoch::with('circle.protocol')->where('circle_id', $circle_id)->where('number', $request->epoch)->first();
        }
        if (!$epoch)
            return 'Epoch Not found';

        return $this->repo->getEpochCsv($epoch, $circle_id, $request->grant);
    }

}
