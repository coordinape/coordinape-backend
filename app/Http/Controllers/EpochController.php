<?php

namespace App\Http\Controllers;

use App\Http\Requests\EpochRequest;
use App\Http\Requests\NewEpochRequest;
use App\Models\Epoch;
use App\Repositories\EpochRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EpochController extends Controller
{

    protected $repo;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
    }

    public function epoches(Request $request, $circle_id): JsonResponse
    {
        return response()->json($this->repo->epoches($request, $circle_id));
    }

    public function getActiveEpochs(Request $request): JsonResponse
    {
        return response()->json($this->repo->getActiveEpochs($request));
    }

    public function updateEpoch(newEpochRequest $request, $circle_id, Epoch $epoch): JsonResponse
    {
        $now = Carbon::now();
        $data = $request->only('start_date', 'grant', 'days', 'repeat');
        $start_date = Carbon::createFromFormat('Y-m-d\TH:i:s.v\Z', $data['start_date']);
        $end_date = $start_date->copy()->addDays($data['days']);

        if ($epoch->circle_id != $circle_id) {
            return response()->json(
                ['message' => "You are not authorized to update this epoch"], 422);
        } else if ($epoch->ended == 1) {
            return response()->json(
                ['message' => "You cannot update an epoch that has ended"], 422);
            // check if an epoch that has started and changing startdate to later than current date
        } else if ($now >= $epoch->start_date && $start_date >= $now) {
            return response()->json(
                ['message' => "You cannot have change the start date to later than now when epoch has already started"], 422);
        }
        if (!empty($data['repeat']) && $data['repeat'] > 0) {
            $repeating = Epoch::where('circle_id', $circle_id)->where('id', '!=', $epoch->id)->where('ended', 0)->where('repeat', '>', 0)->exists();
            if ($repeating) {
                return response()->json(
                    ['message' => "You cannot have more than one repeating active epoch"], 422);
            }
            if ($data['repeat'] == 2) {
                $data['repeat_day_of_month'] = $start_date->day;
            }
        }
        $exist = Epoch::where('id', '!=', $epoch->id)->checkOverlapDatetime(['circle_id' => $circle_id,
            'start_date' => $start_date, 'end_date' => $end_date])->first();
        if ($exist) {
            $startStr = $exist->start_date->format('m/d');
            $endStr = $exist->end_date->format('m/d');
            return response()->json(
                ['message' => "This epoch overlaps with an existing epoch that occurs between $startStr and $endStr\nPlease adjust epoch settings to avoid overlapping with existing epochs"], 422);
        }
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $epoch->update($data);
        return response()->json($epoch);
    }

    public function createEpoch(newEpochRequest $request, $circle_id): JsonResponse
    {
        $data = $request->only('start_date', 'grant', 'days', 'repeat');
        $start_date = Carbon::createFromFormat('Y-m-d\TH:i:s.v\Z', $data['start_date']);
        $end_date = $start_date->copy()->addDays($data['days']);
        if (!empty($data['repeat']) && $data['repeat'] > 0) {
            $repeating = Epoch::where('circle_id', $circle_id)->where('ended', 0)->where('repeat', '>', 0)->exists();
            if ($repeating) {
                return response()->json(
                    ['message' => "You cannot have more than one repeating active epoch"], 422);
            }

            if ($data['repeat'] == 2) {
                $data['repeat_day_of_month'] = $start_date->day;
            }
        }
        $exist = Epoch::checkOverlapDatetime(['circle_id' => $circle_id,
            'start_date' => $start_date, 'end_date' => $end_date])->first();
        if ($exist) {
            $startStr = $exist->start_date->format('m/d');
            $endStr = $exist->end_date->format('m/d');
            return response()->json(
                ['message' => "This epoch overlaps with an existing epoch that occurs between $startStr and $endStr\nPlease adjust epoch settings to avoid overlapping with existing epochs"], 422);
        }
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['circle_id'] = (int)$circle_id;
        $epoch = new Epoch($data);
        $epoch->save();
        return response()->json($epoch);
    }

    public function deleteEpoch(Request $request, $circle_id, Epoch $epoch): JsonResponse
    {
        $today = Carbon::now();
        if ($epoch->circle_id != $circle_id) {
            return response()->json(['message' => 'You are not authorized to delete this epoch'], 403);
        } else if ($epoch->start_date <= $today || $epoch->ended == 1) {
            return response()->json(['message' => 'You cannot delete an epoch that has started or ended'], 403);
        }

        $epoch->delete();
        return response()->json($epoch);
    }

}
