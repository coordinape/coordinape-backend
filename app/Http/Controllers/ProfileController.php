<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\ProfileUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\ProfileRepository;

class ProfileController extends Controller
{
    protected $repo;

    public function __construct(ProfileRepository $repo) {
        $this->repo = $repo;
    }

    public function getProfile(Request $request, $address) : JsonResponse {
        return response()->json($this->repo->getProfile($request,$address));
    }

    public function saveProfile(ProfileRequest $request, $address) {
        return response()->json($this->repo->saveProfile($request, $request->address));
    }

    public function uploadProfileAvatar(ProfileUploadRequest $request, $address) : JsonResponse {
        $profile = $this->repo->uploadProfileAvatar($request, $request->address);
        if($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed' ,422]);
    }

    public function uploadProfileBackground(ProfileUploadRequest $request, $address) : JsonResponse {
       $profile = $this->repo->uploadProfileBackground($request, $request->address);
       if($profile)
           return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed' ,422]);
    }

}
