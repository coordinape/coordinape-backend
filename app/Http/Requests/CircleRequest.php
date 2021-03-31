<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Ethereum\EcRecover;
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
        $circle_id = null;
        $existing_user =  User::byAddress($address)->isAdmin();
        if($this->route('subdomain')) {
            $circle_id = Utils::getCircleIdByName($this->route('subdomain'));
            $existing_user = $existing_user->where('circle_id', $circle_id);
        }
        $existing_user = $existing_user->first();
        $this->merge([
            'user' => $existing_user,
            'circle_id' => $circle_id
        ]);
        return $existing_user && strtolower($recoveredAddress)==strtolower($address);
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'name' => !empty($data['name']) ? $data['name']:null
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
            'name' => 'required|string'
        ];
    }
}
