<?php

namespace App\Http\Controllers;

use App\Http\Requests\CircleRequest;
use App\Models\Circle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\CircleRepository;

class CircleController extends Controller
{
    protected $repo;
    public function __construct(CircleRepository $repo) {
        $this->repo = $repo;
    }

    public function getCircles(Request $request, $circle_id = null): JsonResponse
    {
        return response()->json($this->repo->getCircles($request));
    }

    public function createCircle(CircleRequest $request)
    {
        return response()->json($this->repo->createCircle($request));
    }

    public function updateCircle( CircleRequest $request, $circle_id=null, Circle $circle): JsonResponse
    {
        return response()->json($this->repo->updateCircle($circle, $request));
    }

}
