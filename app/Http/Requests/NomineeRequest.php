<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NomineeRequest extends FormRequest
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
            'name' => !empty($data['name']) ? $data['name']:null,
            'address' => !empty($data['address']) ? strtolower($data['address']):null,
            'description'  => !empty($data['description']) ? $data['description']:null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $circle_id = $this->circle_id;
        return [
            'data' => 'required',
            'name' => 'required|string|max:255',
            'address' => ['required', 'string', 'size:42',Rule::unique('nominees')->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id)->where('ended',0);
            }), Rule::unique('users')->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id)->whereNull('deleted_at');
            })],
            'description' => 'required|string|max:5000',
        ];
    }
}
