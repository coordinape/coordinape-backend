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
        $profile = $request->user();
        $addressProfile = $this->repo->getProfile($address, ['users.circle.protocol', 'users.teammates', 'users.histories.epoch']);
        if ($profile && !$profile->admin_view) {
            if (!$addressProfile || count(array_intersect($profile->circle_ids(), $addressProfile->circle_ids())) < 1) {
                return response()->json(['message' => 'User has no permission to view this profile'], 403);
            }
        }
        return response()->json($addressProfile);
    }

    public function updateMyProfile(ProfileRequest $request): JsonResponse
    {
        return response()->json($this->repo->saveProfile($request));
    }

    public function uploadMyProfileAvatar(ProfileUploadRequest $request): JsonResponse
    {
        $profile = $this->repo->uploadProfileAvatar($request);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    public function uploadMyProfileBackground(ProfileUploadRequest $request): JsonResponse
    {
        $profile = $this->repo->uploadProfileBackground($request);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    // to be deprecated
    public function uploadProfileAvatar(ProfileUploadRequest $request, $address): JsonResponse
    {
        $profile = $this->repo->uploadProfileAvatar($request, $request->address);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    // to be deprecated
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
        if (!$profile || count($profile->users) == 0)
            abort('403', 'You do not have an active account in Coordinape');

        $profile->tokens()->delete();
        $token = $profile->createToken('circle-access-token', ['read'])->plainTextToken;
        return response()->json(compact('token', 'profile'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true]);
    }
}
