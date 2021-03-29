<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Ethereum\EcRecover;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        return  strtolower($recoveredAddress)==strtolower($address);
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $existing_user = null;
        if($this->route('address')) {
            $existing_user =  User::byAddress($this->route('address'));
            if($this->route('subdomain')) {
                $existing_user = $existing_user->where('circle_id', $this->route('subdomain'));
            }
            $existing_user = $existing_user->first();
        }

        $this->merge([
            'data' => $data,
            'user' => $existing_user,
            'name' => !empty($data['name']) ? $data['name']:null,
            'circle_id' => !empty($data['circle_id']) ? $data['circle_id']:null,
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

        if($this->method()=="POST") {
            $address .= '|unique:users';
        }
        else if($this->user) {
            $address .= '|unique:users,id,'.$this->user->id;
        }

        return [
            'data' => 'required',
            'name' => 'required',
            'circle_id' => 'required|integer|exists:circles,id',
            'address' => $address
        ];
    }
}
