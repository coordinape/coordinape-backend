<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class TeammatesRequest extends FormRequest
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

        $circle_id = $this->route('circle_id');
        $existing_user =  User::byAddress($address);
        if($circle_id) {
            $existing_user = $existing_user->where('circle_id', $circle_id);
        }
        $existing_user = $existing_user->first();
        $hash = $this->get('hash');
        $valid_signature = Utils::validateSignature($address, $data, $signature, $hash);

        $this->merge([
            'user' => $existing_user,
            'circle_id' => $circle_id,
            'address' => $address,
        ]);

        return $existing_user && $valid_signature;
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'teammates' => $data['teammates']
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
//        $user = $this->user;
//        $activeEpoch = $user->circle->epoches()->isActiveDate()->first();
//        if($activeEpoch && $activeEpoch->is_regift_phase) {
//            throw new ConflictHttpException('Not allowed to edit teammates in regifting phase');
//        }
        return ['teammates.*'=> 'integer'];
    }
}
