<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\ProfileUploadRequest;
use App\Repositories\CircleRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $repo, $circleRepo;

    public function __construct(ProfileRepository $repo, CircleRepository $circleRepo)
    {
        $this->repo = $repo;
        $this->circleRepo = $circleRepo;
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

    public function uploadMyProfileAvatar(ProfileUploadRequest $request, $address = null): JsonResponse
    {
        $profile = $this->repo->uploadProfileAvatar($request);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    public function uploadMyProfileBackground(ProfileUploadRequest $request, $address = null): JsonResponse
    {
        $profile = $this->repo->uploadProfileBackground($request);
        if ($profile)
            return response()->json($profile);

        return response()->json(['message' => 'File Upload Failed', 422]);
    }

    public function manifest(Request $request): JsonResponse
    {
        $profile = auth('sanctum')->user() ?: $this->repo->getProfile($request->get('address'));
        $circle = null;
        if (!$profile) {
            $profile = $this->repo->createInitialProfile($request->get('address'));
        } else {
            $circle_id = $request->get('circle_id');
            if (!$circle_id) {
                $circle_ids = $profile->circle_ids();
                $circle_id = count($circle_ids) ? $circle_ids[0] : null;
            }
            if ($circle_id)
                $circle = $this->circleRepo->fullCircleData($profile, $request, $circle_id);
        }

        return response()->json(compact('profile', 'circle') + $this->repo->getCircleDataWithProfile($profile));
    }

    public function login(Request $request): JsonResponse
    {
        $profile = $this->repo->getProfile($request->get('address'));
        if (!$profile) {
            $profile = $this->repo->createInitialProfile($request->get('address'));
        } else {
            $profile->tokens()->delete();
        }
        $token = $profile->createToken('circle-access-token', ['read'])->plainTextToken;
        return response()->json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true]);
    }
}
