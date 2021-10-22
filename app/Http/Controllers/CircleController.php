<?php

namespace App\Http\Controllers;

use App\Http\Requests\CircleRequest;
use App\Http\Requests\CreateCircleRequest;
use App\Http\Requests\FileUploadRequest;
use App\Models\Circle;
use App\Repositories\CircleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CircleController extends Controller
{
    protected $repo;

    public function __construct(CircleRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getCircles(Request $request, $circle_id = null): JsonResponse
    {
        return response()->json($this->repo->getCircles($request));
    }

    public function createCircle(CreateCircleRequest $request): JsonResponse
    {
        return response()->json($this->repo->createCircle($request));
    }

    public function getWebhook(Request $request, $circle_id): JsonResponse
    {
        return response()->json($this->repo->getWebhook($circle_id));
    }

    public function updateCircle(CircleRequest $request, $circle_id, Circle $circle): JsonResponse
    {
        return response()->json($this->repo->updateCircle($circle, $request));
    }

    public function uploadCircleLogo(FileUploadRequest $request, $circle_id): JsonResponse
    {

        $circle = $this->repo->uploadCircleLogo($request);
        if ($circle)
            return response()->json($circle);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    public function userWithFullCircleData(Request $request): JsonResponse
    {
        $circle_id = $request->get('circle_id');
        if ($circle_id) {
            $user = $this->repo->userWithCircleData($request, $circle_id);
            if ($user) {
                return response()->json($user);
            }
        }

        return response()->json(['message' => 'Please provide a circle id that the user is part of'], 403);
    }
}
