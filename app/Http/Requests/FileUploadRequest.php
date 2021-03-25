<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
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
        $is_user = User::byAddress($address)->first();
        return $is_user && strtolower($recoveredAddress)==$address;
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
            'address' => 'required',
            'signature' => 'required',
            'data' => 'required'
        ];
    }
}
