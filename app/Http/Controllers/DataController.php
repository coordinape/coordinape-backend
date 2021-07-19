<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewGiftRequest;
use App\Models\Profile;
use App\Notifications\AddNewUser;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\PendingTokenGift;
use App\Http\Requests\UserRequest;
use DB;
use App\Models\TokenGift;
use App\Repositories\EpochRepository;
use App\Http\Requests\CsvRequest;
use App\Http\Requests\TeammatesRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use App\Http\Requests\FileUploadRequest;
use App\Helper\Utils;
use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\AdminUserRequest;
use App\Models\Epoch;
use App\Models\Protocol;
use App\Models\Burn;
use App\Http\Requests\DeleteUserRequest;

class DataController extends Controller
{
    protected $repo ;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getProtocols(Request $request): JsonResponse
    {
        return response()->json(Protocol::all());
    }

    public function newUpdateGifts(NewGiftRequest $request, $circle_id, $address): JsonResponse
    {
        $user = $request->user;
        $this->repo->newUpdateGifts($request, $address);
        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function getPendingGifts(Request $request, $circle_id = null): JsonResponse {
        $filters = $request->all();

        if($circle_id) {
            $filters['circle_id'] = $circle_id;
        }
        else if(empty($filters['circle_id']))
        {
            return response()->json([]);
        }

        if(!empty($filters['recipient_address'])) {
            $user = User::byAddress($request->recipient_address)->where('circle_id',$circle_id)->first();
            $filters['recipient_id'] = $user->id;
        }

        if(!empty($filters['sender_address'])) {
            $user = User::byAddress($request->sender_address)->where('circle_id',$circle_id)->first();
            $filters['sender_id'] = $user->id;
        }

        return response()->json(PendingTokenGift::filter($filters)->get());
    }

    public function getGifts(Request $request, $circle_id = null): JsonResponse {
        $filters = $request->all();
        if($circle_id) {
            $filters['circle_id'] = $circle_id;
        }

        if(!empty($filters['recipient_address'])) {
            $user = User::byAddress($request->recipient_address)->where('circle_id',$circle_id)->first();
            $filters['recipient_id'] = $user->id;
        }

        if(!empty($filters['sender_address'])) {
            $user = User::byAddress($request->sender_address)->where('circle_id',$circle_id)->first();
            $filters['sender_id'] = $user->id;
        }

        return response()->json( Utils::queryCache($request,function () use($filters,$request) {
            return TokenGift::filter($filters)->limit(20000)->get();
        }, 60, $circle_id));
    }

    public function updateTeammates(TeammatesRequest $request, $circle_id=null) : JsonResponse {

        $user = $request->user;
        $teammates = $request->teammates;
        $circle_teammates = User::where('circle_id', $request->circle_id)->where('is_hidden',0)->where('id','<>',$user->id)->whereIn('id',$teammates)->pluck('id');
        DB::transaction(function () use ($circle_teammates, $user) {
            $this->repo->resetGifts($user, $circle_teammates);
            if ($circle_teammates) {
                $user->teammates()->sync($circle_teammates);
            }
        });
        $user->load(['teammates','pendingSentGifts']);
        return response()->json($user);
    }

    public function generateCsv(CsvRequest $request, $circle_id = null)
    {
        if (!$circle_id) {
            if (!$request->circle_id)
                return response()->json(['error' => 'Circle not Found'], 422);
            $circle_id = $request->circle_id;
        }

        $epoch = null;
        if($request->epoch_id) {
            $epoch = Epoch::with('circle.protocol')->where('circle_id',$circle_id)->where('id',$request->epoch_id )->first();

        } else if ($request->epoch) {
            $epoch = Epoch::with('circle.protocol')->where('circle_id',$circle_id)->where('number', $request->epoch)->first();
        }
        if(!$epoch)
            return 'Epoch Not found';

        return $this->repo->getEpochCsv($epoch, $circle_id, $request->grant);
    }

    public function uploadAvatar(FileUploadRequest $request, $circle_id=null) : JsonResponse {

        $file = $request->file('file');
        $resized = Image::make($request->file('file'))
            ->resize(100, null, function ($constraint) { $constraint->aspectRatio(); } )
            ->encode($file->getCLientOriginalExtension(),80);
        $new_file_name = Str::slug(pathinfo(basename($file->getClientOriginalName()), PATHINFO_FILENAME)).'_'.time().'.'.$file->getCLientOriginalExtension();
        $ret = Storage::put($new_file_name, $resized);
        if($ret) {
            $user = User::byAddress($request->get('address'))->where('circle_id',$request->circle_id)->first();
            if($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }

            $user->avatar = $new_file_name;
            $user->save();
            return response()->json($user);
        }

        return response()->json(['error' => 'File Upload Failed' ,422]);
    }

     public function burns(Request $request, $circle_id) : JsonResponse  {
         $burns = Burn::where('circle_id',$circle_id)->filter($request->all())->get();
         return response()->json($burns);
     }


}
