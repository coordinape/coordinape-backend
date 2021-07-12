<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class EpochRequest extends FormRequest
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
        $existing_user =  User::byAddress($address)->isAdmin();
        if(!$this->route('subdomain'))
            return false;

        $circle_id = $this->route('subdomain');
        $existing_user = $existing_user->where('circle_id', $circle_id)->first();

        $this->merge([
            'user' => $existing_user,
            'circle_id' => $circle_id
        ]);
        $recoveredAddressWC = Utils::personalEcRecover($data,$signature, false);
        return $existing_user && (strtolower($recoveredAddress)==strtolower($address) || $recoveredAddressWC == strtolower($address));
    }

    protected function prepareForValidation() {

        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'start_date' => !empty($data['start_date']) ? $data['start_date']:null,
            'end_date' => !empty($data['end_date']) ? $data['end_date']:null,
            'grant' => !empty($data['grant']) ? $data['grant']:0
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'grant' => 'numeric|max:1000000000'
        ];
    }
}
