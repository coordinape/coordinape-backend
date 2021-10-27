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
            return Utils::queryCache($request, function () use ($epoch_id, $circle_id, $user) {
                $query = $this->tokenModel->fromCircle($circle_id)->where(function ($q) use ($user) {
                    $q->where('sender_id', '<>', $user->id)->orWhere('recipient_id', '<>', $user->id);
                });
                $queryUserGives = $this->tokenModel->fromCircle($circle_id)->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                });

                if ($epoch_id) {
                    $query->fromEpochId($epoch_id);
                    $queryUserGives->fromEpochId($epoch_id);
                }
                $givesWithoutUser = $query->selectWithoutNote()->get();
                return $givesWithoutUser->merge($queryUserGives->selectWithNoteAddress()->get());
            }, 60, $circle_id);
        }

        return null;
    }

    public function newGetPendingGifts($request, $circle_id)
    {

        $profile = $request->user();
        $user = $profile->users()->where('circle_id', $circle_id)->first();
        if ($user) {
            $query = $this->pendingTokenModel->fromCircle($circle_id)->where(function ($q) use ($user) {
                $q->where('sender_id', '<>', $user->id)->orWhere('recipient_id', '<>', $user->id);
            });
            $queryUserGives = $this->pendingTokenModel->fromCircle($circle_id)->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
            });

            $givesWithoutUser = $query->selectWithoutNote()->get();
            return $givesWithoutUser->merge($queryUserGives->selectWithNoteAddress()->get());
        }

        return null;
    }

    public function getGifts($request, $circle_id = null, $without_notes = false)
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
        $query = $this->tokenModel->filter($filters);
        if ($without_notes)
            $query->selectWithoutNote();

        return Utils::queryCache($request, function () use ($filters, $request, $query) {
            return $query->limit(30000)->get();
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

        return $query->get();
    }
}
