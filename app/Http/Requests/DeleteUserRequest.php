<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    public function authorize()
    {
        $data = $this->get('data');
        $signature = $this->get('signature');
        $address  = $this->get('address');
        $circle_id =  $this->route('circle_id');
        $updating_user = null;
        $admin_user = null;
        if($circle_id) {
            $admin_user = User::byAddress($address)->isAdmin()->where('circle_id', $circle_id)->first();
            $updating_user = User::byAddress($this->route('address'))->where('circle_id', $circle_id)->first();
        }
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $this->merge([
            'user' => $updating_user
        ]);
        $recoveredAddressWC = Utils::personalEcRecover($data,$signature, false);

        return $admin_user && (strtolower($recoveredAddress)==strtolower($address) || $recoveredAddressWC == strtolower($address));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
