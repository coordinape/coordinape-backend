<?php

namespace App\Http\Controllers;

use App\Helper\Utils;
use App\Http\Requests\CsvRequest;
use App\Http\Requests\NewGiftRequest;
use App\Http\Requests\TeammatesRequest;
use App\Models\Epoch;
use App\Models\PendingTokenGift;
use App\Models\Protocol;
use App\Models\TokenGift;
use App\Models\User;
use App\Repositories\EpochRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class DataController extends Controller
{
    protected $repo;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
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
        $filters = $request->all();

        if ($circle_id) {
            $filters['circle_id'] = $circle_id;
        } else if (empty($filters['circle_id'])) {
            return response()->json([]);
        }

        if (!empty($filters['recipient_address'])) {
            $user = User::byAddress($request->recipient_address)->where('circle_id', $circle_id)->first();
            $filters['recipient_id'] = $user->id;
        }

        if (!empty($filters['sender_address'])) {
            $user = User::byAddress($request->sender_address)->where('circle_id', $circle_id)->first();
            $filters['sender_id'] = $user->id;
        }

        return response()->json(PendingTokenGift::filter($filters)->get());
    }

    public function newGetGifts(Request $request)
    {
        $data = $request->all();
        if (!empty($data['circle_id'])) {
            $profile = $request->user();
            $user = $profile->users()->where('circle_id', $data['circle_id'])->first();
            if ($user) {
                return response()->json(Utils::queryCache($request, function () use ($data, $request) {
                    $query = TokenGift::fromCircle($data['circle_id']);
                    if (!empty($data['epoch_id'])) {
                        $query->fromEpochId($data['epoch_id']);
                    }
                    return $query->select(['id', 'recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'dts_created'])->get();
                }, 60, $data['circle_id']));
            }
        }
        return response()->json(['message' => 'Please provide a circle id that the user is part of'], 403);

    }

    public function getGifts(Request $request, $circle_id = null): JsonResponse
    {
        $filters = $request->all();
        if ($circle_id) {
            $filters['circle_id'] = $circle_id;
        }

        if ($circle_id && !empty($filters['latest_epoch']) && $filters['latest_epoch'] == 1) {
            $query = Epoch::where('circle_id', $circle_id)->where('ended', 1)->orderBy('number', 'desc');
            if (!empty($filters['timestamp'])) {
                $before_date = Carbon::createFromTimestamp($filters['timestamp']);
                $query->where('end_date', '<=', $before_date);
            }
            $epoch = $query->first();

            if ($epoch) {
                $filters['epoch_id'] = $epoch->id;
            } else {
                return response()->json([]);
            }
        }

        if (!empty($filters['recipient_address'])) {
            $user = User::byAddress($request->recipient_address)->where('circle_id', $circle_id)->first();
            if ($user) {
                $filters['recipient_id'] = $user->id;
            }
        }

        if (!empty($filters['sender_address'])) {
            $user = User::byAddress($request->sender_address)->where('circle_id', $circle_id)->first();
            if ($user) {
                $filters['sender_id'] = $user->id;
            }
        }

        return response()->json(Utils::queryCache($request, function () use ($filters, $request) {
            return TokenGift::filter($filters)->limit(20000)->get();
        }, 60, $circle_id));
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
