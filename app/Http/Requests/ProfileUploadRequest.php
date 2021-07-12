<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUploadRequest extends FormRequest
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
        $address  = strtolower($this->route('address'));
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $user =  User::byAddress($address)->first();
        $this->merge([
            'user' => $user,
        ]);
        $recoveredAddressWC = Utils::personalEcRecover($data,$signature, false);
        return $user && (strtolower($recoveredAddress)==strtolower($address) || $recoveredAddressWC == strtolower($address));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|image|max:10240',
        ];
    }
}
