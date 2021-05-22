<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Models\User;
use App\Helper\Utils;

class GiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $data = $this->get('data');
        $signature = $this->get('signature');
        $address  = strtolower($this->get('address'));
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $existing_user = null;
        $circle_id = null;
        if($this->route('address')) {
            $existing_user =  User::byAddress($this->route('address'));
            if($this->route('subdomain')) {
                $circle_id = Utils::getCircleIdByName($this->route('subdomain'));
                $existing_user = $existing_user->where('circle_id', $circle_id);
            }
            $existing_user = $existing_user->first();
        }
        $this->merge([
            'user' => $existing_user,
            'circle_id' => $circle_id
        ]);
        return $existing_user && strtolower($recoveredAddress)==$address;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'gifts' => json_decode($this->get('data'), true)
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'data' => 'required',
            'signature' => 'required',
            'address' => 'required',
            'gifts.*.recipient_address' => 'required|string|size:42',
            'gifts.*.tokens' => 'required|integer'
        ];

        $gifts = $this->gifts;
        if(!$gifts)
            throw new ConflictHttpException('data cannot be null');

        $sum = array_reduce($gifts, function($carry, $item)
        {
            return $carry + $item['tokens'];
        });

        $user = $this->user;
        if(!$user)
            throw new ConflictHttpException('User cannot be found');

//        $this->merge(['user' => $user]);

        if($sum > $user->starting_tokens) {
            throw new ConflictHttpException('Sum of tokens is more than '. $user->starting_tokens);
        }

        $activeEpoch = $user->circle->epoches()->isActiveDate()->first();
        if(!$activeEpoch) {
            throw new ConflictHttpException('Currently not in an active Epoch');
        }
        else if($activeEpoch->is_regift_phase == 1) {
            throw new ConflictHttpException("Not allowed to allocate in regifting phase");
        }

        return $rules;
    }
}
