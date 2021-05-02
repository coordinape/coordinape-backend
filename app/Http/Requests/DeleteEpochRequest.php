<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteEpochRequest extends FormRequest
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
        $circle_id = $this->route('subdomain');
        if($circle_id) {
            $admin_user = User::byAddress($this->get('address'))->isAdmin()->where('circle_id', $circle_id)->first();
        } else {
            return false;
        }
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $this->merge([
            'circle_id' => $circle_id
        ]);
        return $admin_user && strtolower($recoveredAddress)==strtolower($address);
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
