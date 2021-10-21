<?php

namespace App\Http\Controllers;

use App\Helper\Utils;
use App\Http\Requests\NomineeRequest;
use App\Http\Requests\VouchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\NominationRepository;

class NominationController extends Controller
{
    protected $repo;

    public function __construct(NominationRepository $repo) {
        $this->repo = $repo;
    }

    public function getNominees(Request $request, $circle_id) : JsonResponse {
        return response()->json($this->repo->getNominees($request,$circle_id));
    }

    public function addVouch(VouchRequest $request, $circle_id) : JsonResponse {
        if($request->user->circle->only_giver_vouch == 1 && $request->user->non_giver == 1) {
            return response()->json(['message'=> 'Non-givers are not allowed to vouch for users'],403);
        }
        return response()->json($this->repo->addVouch($request,$circle_id));
    }

    public function createNominee(NomineeRequest $request, $circle_id) : JsonResponse {
        if($request->user->circle->only_giver_vouch == 1 && $request->user->non_giver == 1) {
            return response()->json(['message'=> 'Non-givers are not allowed to nominate users'],403);
        }
        return response()->json($this->repo->createNominee($request));
    }
}
