<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

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
        $subdomain = $this->route('subdomain');
        $circle_id = Utils::getCircleIdByName($subdomain);
        $updating_user = null;
        $admin_user = null;
        if($circle_id) {
            $admin_user = User::byAddress($this->get('address'))->isAdmin()->where('circle_id', $circle_id)->first();
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
            'address' => !empty($data['address']) ? $data['address']:null
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $address = 'required|string|size:42';

//        if($this->method()=="POST") {
//            $address .= '|unique:users';
//        }
//        else if($this->user) {
//            $address .= '|unique:users,id,'.$this->user->id;
//        }

        return [
            'data' => 'required',
            'name' => 'required',
//            'circle_id' => 'required|integer|exists:circles,id',
            'address' => $address
        ];
    }
}
