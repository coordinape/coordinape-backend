<?php

namespace App\Http\Controllers;

use App\Http\Requests\CircleRequest;
use App\Http\Requests\FileUploadRequest;
use App\Models\Circle;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\CircleRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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

    public function updateCircle(CircleRequest $request, $circle_id, Circle $circle): JsonResponse
    {
        return response()->json($this->repo->updateCircle($circle, $request));
    }

    public function uploadCircleLogo(FileUploadRequest $request, $circle_id) : JsonResponse {

        $file = $request->file('file');
        $resized = Image::make($request->file('file'))
            ->resize(100, null, function ($constraint) { $constraint->aspectRatio(); } )
            ->encode($file->getCLientOriginalExtension(),80);
        $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)).'_'.time().'.'.$file->getCLientOriginalExtension();
        $ret = Storage::put($new_file_name, $resized);
        if($ret) {
            $circle = $request->user->circle;
            if($circle->logo && Storage::exists($circle->logo)) {
                Storage::delete($circle->logo);
            }
            $circle->logo = $new_file_name;
            $circle->save();
            return response()->json($circle);
        }
        return response()->json(['error' => 'File Upload Failed' ,422]);
    }

}
