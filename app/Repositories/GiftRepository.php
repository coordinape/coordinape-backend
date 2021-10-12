<?php

namespace App\Repositories;

use App\Helper\Utils;
use App\Models\Epoch;
use App\Models\PendingTokenGift;
use App\Models\TokenGift;
use App\Models\User;
use Carbon\Carbon;

class GiftRepository
{

    protected $pendingTokenModel, $tokenModel;

    public function __construct(PendingTokenGift $pendingTokenModel, TokenGift $tokenModel)
    {
        $this->pendingTokenModel = $pendingTokenModel;
        $this->tokenModel = $tokenModel;
    }

    public function newGetGifts($request, $circle_id)
    {

        $profile = $request->user();
        $user = $profile->users()->where('circle_id', $circle_id)->first();
        if ($user) {
            $epoch_id = $request->get('epoch_id');
            return Utils::queryCache($request, function () use ($epoch_id, $circle_id) {
                $query = $this->tokenModel->fromCircle($circle_id);
                if ($epoch_id) {
                    $query->fromEpochId($epoch_id);
                }
                return $query->select(['id', 'recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'dts_created'])->get();
            }, 60, $circle_id);
        }

        return null;
    }

    public function getGifts($request, $circle_id = null)
    {

        $filters = $request->all();

        if ($circle_id) {
            $filters['circle_id'] = $circle_id;
        }

        if ($circle_id && !empty($filters['latest_epoch']) && $filters['latest_epoch'] == 1) {
            $query = Epoch::where('circle_id', $circle_id)->where('ended', 1)->orderBy('number', 'desc');
            if (!empty($filters['timestamp'])) {
                $before_date = Carbon::createFromTimestamp($filters['timestamp']);
                $query->where('end_date', '<=', $before_date);
            }
            $epoch = $query->first();

            if ($epoch) {
                $filters['epoch_id'] = $epoch->id;
            } else {
                return [];
            }
        }

        if (!empty($filters['recipient_address'])) {
            $user = User::byAddress($request->recipient_address)->where('circle_id', $circle_id)->first();
            if ($user) {
                $filters['recipient_id'] = $user->id;
            }
        }

        if (!empty($filters['sender_address'])) {
            $user = User::byAddress($request->sender_address)->where('circle_id', $circle_id)->first();
            if ($user) {
                $filters['sender_id'] = $user->id;
            }
        }
        $profile = $request->user();
        $query = $this->tokenModel->filter($filters);
        if ($profile && !$profile->admin_view) {
            return $query->whereIn('circle_id', $profile->currentAccessToken()->abilities)->selectWithoutNote()->limit(35000)->get();
        }

        return Utils::queryCache($request, function () use ($filters, $request, $query) {
            return $query->selectWithoutNote()->limit(35000)->get();
        }, 60, $circle_id);
    }

    public function getPendingGifts($request, $circle_id = null)
    {

        $filters = $request->all();

        if ($circle_id) {
            $filters['circle_id'] = $circle_id;
        } else if (empty($filters['circle_id'])) {
            return [];
        }

        if (!empty($filters['recipient_address'])) {
            $user = User::byAddress($request->recipient_address)->where('circle_id', $circle_id)->first();
            $filters['recipient_id'] = $user->id;
        }

        if (!empty($filters['sender_address'])) {
            $user = User::byAddress($request->sender_address)->where('circle_id', $circle_id)->first();
            $filters['sender_id'] = $user->id;
        }
        $query = $this->pendingTokenModel->filter($filters);
        $profile = $request->user();
        if ($profile && !$profile->admin_view) {
            $query->whereIn('circle_id', $profile->currentAccessToken()->abilities);
        }

        return $query->select(['id', 'recipient_address', 'sender_address', 'recipient_id', 'sender_id', 'tokens', 'circle_id', 'epoch_id', 'created_at', 'updated_at'])->get();
    }
}
