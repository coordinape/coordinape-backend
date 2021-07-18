<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VouchRequest extends FormRequest
{
    public function authorize()
    {
        $data = $this->get('data');
        $signature = $this->get('signature');
        $address  = $this->get('address');
        $circle_id =  $this->route('circle_id');
        $user = User::with('circle')->byAddress($address)->where('circle_id', $circle_id)->first();
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $this->merge([
            'user' => $user,
            'circle_id' => $circle_id,
            'circle' => $user->circle
        ]);
        $recoveredAddressWC = Utils::personalEcRecover($data,$signature, false);
        return $user && (strtolower($recoveredAddress)==strtolower($address) || $recoveredAddressWC == strtolower($address));
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'nominee_id' => !empty($data['nominee_id']) ? $data['nominee_id']:null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user_id = $this->user->id;
        $nominee_id = $this->nominee_id;

        return [
            'nominee_id' => ['required', 'integer', Rule::unique('vouches')
                ->where(function ($query) use ($user_id, $nominee_id) {
                    return $query->where('voucher_id', $user_id)->where('nominee_id', $nominee_id);
                })]
        ];
    }
}
