<?php

namespace App\Http\Controllers;

use App\Http\Requests\CircleRequest;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\UpdateWebhookRequest;
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

    public function getWebhook(Request $request, $circle_id) {
        return response()->json($this->repo->getWebhook($circle_id));
    }

    public function updateCircle(CircleRequest $request, $circle_id, Circle $circle): JsonResponse
    {
        return response()->json($this->repo->updateCircle($circle, $request));
    }

    public function uploadCircleLogo(FileUploadRequest $request, $circle_id) : JsonResponse {

        $circle = $this->repo->uploadCircleLogo($request);
        if($circle)
            return response()->json($circle);

        return response()->json(['message'=> 'File Upload Failed' ,422]);
    }

    public function updateWebhook(UpdateWebhookRequest $request, $circle_id) {
      return response()->json($this->repo->updateWebhook($request, $circle_id));
    }
}
