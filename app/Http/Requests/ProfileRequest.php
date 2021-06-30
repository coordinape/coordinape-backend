<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
        $address  = $this->route('address');
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        return  strtolower($recoveredAddress)==strtolower($address);
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'skills' => !empty($data['skills']) ? $data['skills']:null,
            'github_username' => !empty($data['github_username']) ? $data['github_username']:null,
            'telegram_username' => !empty($data['telegram_username']) ? $data['telegram_username']:null,
            'discord_username' => !empty($data['discord_username']) ? $data['discord_username']:null,
            'medium_username' => !empty($data['medium_username']) ? $data['medium_username']:null,
            'website' => !empty($data['website']) ? $data['website']:null
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
            'data' => 'required'
        ];
    }
}
