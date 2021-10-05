<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\ProfileUploadRequest;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $repo;

    public function __construct(ProfileRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getProfile(Request $request, $address): JsonResponse
    {
        return response()->json($this->repo->getProfile($address, ['users.circle.protocol', 'users.teammates', 'users.histories.epoch']));
    }

    public function updateMyProfile(ProfileRequest $request): JsonResponse
    {
        return response()->json($this->repo->saveProfile($request));
    }

    public function uploadProfileAvatar(ProfileUploadRequest $request, $address): JsonResponse
    {
        $profile = $this->repo->uploadProfileAvatar($request, $request->address);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    public function uploadProfileBackground(ProfileUploadRequest $request, $address): JsonResponse
    {
        $profile = $this->repo->uploadProfileBackground($request, $request->address);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    public function manifest(Request $request): JsonResponse
    {
        $profile = $this->repo->getProfile($request->get('address'), ['users.circle.epoches', 'users.circle.nominees']);
        if (!$profile && count($profile->users))
            abort('403', 'You do not have an active account in Coordinape');

        if ($profile->admin_view) {
            $circle_ids = ['*'];
        } else {
            $circle_ids = $profile->users->pluck('circle_id')->toArray();
        }
        $profile->tokens()->delete();
        $token = $profile->createToken('circle-access-token', $circle_ids)->plainTextToken;
        return response()->json(compact('token', 'profile'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(true);
    }

    public function testtoken(Request $request): JsonResponse
    {
        $profile = $request->user();
        $token = $profile->currentAccessToken();
        dd($token);
    }
}
