<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CircleRequest extends FormRequest
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
        $address  = $this->get('address');
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $circle_id = $this->route('circle_id');
        $existing_user =  User::byAddress($address)->isAdmin();
        if($circle_id) {
            $existing_user = $existing_user->where('circle_id', $circle_id);
        }
        $existing_user = $existing_user->first();
        $this->merge([
            'user' => $existing_user,
            'circle_id' => $circle_id
        ]);
        $recoveredAddressWC = Utils::personalEcRecover($data,$signature, false);

        return $existing_user && (strtolower($recoveredAddress)==strtolower($address) || $recoveredAddressWC == strtolower($address));
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'name' => !empty($data['name']) ? $data['name']:null,
            'token_name' => !empty($data['token_name']) ? $data['token_name']:null,
            'team_sel_text' => !empty($data['team_sel_text']) ? $data['team_sel_text']:null,
            'alloc_text' => !empty($data['alloc_text']) ? $data['alloc_text']:null,
            'vouching'  => !empty($data['vouching']) ? $data['vouching']:1,
            'min_vouches'  => !empty($data['min_vouches']) ? $data['min_vouches']:3,
            'nomination_days_limit' => !empty($data['nomination_days_limit']) ? $data['nomination_days_limit']:14,
            'vouching_text'  => !empty($data['vouching_text']) ? $data['vouching_text']:null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'data' => 'required',
            'name' => 'required|string|max:255',
            'token_name' => 'required|string|max:255',
            'vouching' => 'integer|min:0|max:1',
            'min_vouches' => 'integer|min:1',
            'nomination_days_limit' => 'integer|min:1',
            'vouching_text' => 'string:max:5000',
            'alloc_text' => 'string:max:5000',
        ];
    }
}
