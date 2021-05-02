<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
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
        $circle_id =  $this->route('subdomain');
        $updating_user = null;
        $admin_user = null;
        if($circle_id) {
            $admin_user = User::byAddress($address)->isAdmin()->where('circle_id', $circle_id)->first();
            $updating_user = User::byAddress($this->route('address'))->where('circle_id', $circle_id)->first();
        }
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $this->merge([
            'user' => $updating_user,
            'circle_id' => $circle_id
        ]);
        return $admin_user && $updating_user && strtolower($recoveredAddress)==strtolower($address);
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'name' => !empty($data['name']) ? $data['name']:null,
            'address' => !empty($data['address']) ? strtolower($data['address']):null,
            'non_giver'  => !empty($data['non_giver']) ? $data['non_giver']:0,
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
            'name' => 'required',
            'address' => ['required', 'string', 'size:42',Rule::unique('users')->ignore($this->user->id)->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id);
            })]
        ];
    }
}
