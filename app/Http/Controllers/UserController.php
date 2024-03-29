<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\BulkUserCreateRequest;
use App\Http\Requests\BulkUserDeleteRequest;
use App\Http\Requests\BulkUserRestoreRequest;
use App\Http\Requests\BulkUserUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Repositories\EpochRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $repo, $epochRepo;

    public function __construct(UserRepository $repo, EpochRepository $epochRepo)
    {
        $this->repo = $repo;
        $this->epochRepo = $epochRepo;
    }

    public function getUsers(Request $request, $circle_id = null): JsonResponse
    {
        return response()->json($this->repo->getUsers($request, $circle_id));
    }

    public function createUser(AdminCreateUserRequest $request, $circle_id): JsonResponse
    {
        return response()->json($this->repo->createUser($request, $circle_id));
    }

    public function updateMyUser(UserRequest $request): JsonResponse
    {
        $user = $request->user;
        if (!$user)
            return response()->json(['message' => 'Address not found'], 422);

        $data = $request->only('name', 'non_receiver', 'bio', 'epoch_first_visit');
        if ($user->fixed_non_receiver == 1) {
            $data['non_receiver'] = 1;
        }
        $user = $this->repo->updateUserData($user, $data);
        return response()->json($user);
    }

    public function adminUpdateUser(AdminUserRequest $request, $circle_id, $address): JsonResponse
    {
        $user = $request->user;
        if (!$user)
            return response()->json(['message' => 'Address not found'], 422);


        if ($user->is_coordinape_user)
            return response()->json(['message' => 'This user is not modifiable'], 422);

        $data = $request->only('name', 'address', 'starting_tokens', 'non_giver', 'fixed_non_receiver', 'role', 'non_receiver');

        if ($data['fixed_non_receiver'] == 1) {
            $data['non_receiver'] = 1;
        }
        if ($user->starting_tokens != $data['starting_tokens']) {
            if ($user->circle->epoches()->isActiveDate()->first()) {
                return response()->json(['message' => 'Cannot update starting tokens during an active epoch'], 422);
            } else {
                $data['give_token_remaining'] = $data['starting_tokens'];
            }
        }
        $data['address'] = strtolower($data['address']);
        $user = $this->repo->updateUserData($user, $data);
        return response()->json($user);
    }

    public function deleteUser(Request $request, $circle_id, $address): JsonResponse
    {
        $user = $request->user;
        if (!$user)
            return response()->json(['message' => 'User not found'], 422);

        $data = $this->repo->deleteUser($user);
        if (is_null($data)) {
            $error = ValidationException::withMessages([
                'failed' => ['Delete user failed please try again'],
            ]);
            throw $error;
        } else {
            return response()->json($data);
        }
    }

    public function bulkCreate(BulkUserCreateRequest $request): JsonResponse
    {
        return response()->json($this->repo->bulkCreate($request));
    }

    public function bulkUpdate(BulkUserUpdateRequest $request): JsonResponse
    {
        return response()->json($this->repo->bulkUpdate($request));
    }

    public function bulkDelete(BulkUserDeleteRequest $request): JsonResponse
    {
        return response()->json(['success' => $this->repo->bulkDelete($request)]);
    }

    public function bulkRestore(BulkUserRestoreRequest $request): JsonResponse
    {
        return response()->json([$this->repo->bulkRestore($request)]);
    }
}
