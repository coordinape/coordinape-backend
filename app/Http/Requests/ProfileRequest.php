<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Profile;

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
        $address  = $this->get('address');
        $user = User::byAddress($address)->first();
        $hash = $this->get('hash');
        $valid_signature = Utils::validateSignature($address, $data, $signature, $hash);
        return $user && $valid_signature;
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $profile = Profile::byAddress($this->get('address'))->first();
        $this->merge([
            'data' => $data,
            'profile' => $profile,
            'bio' => !empty($data['bio']) ? $data['bio']:null,
            'skills' => !empty($data['skills']) ? $data['skills']:null,
            'github_username' => !empty($data['github_username']) ? $data['github_username']:null,
            'telegram_username' => !empty($data['telegram_username']) ? $data['telegram_username']:null,
            'discord_username' => !empty($data['discord_username']) ? $data['discord_username']:null,
            'twitter_username' => !empty($data['twitter_username']) ? $data['twitter_username']:null,
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
        $profile_id = $this->profile ? $this->profile->id:null;
        return [
            'telegram_username' => ['string', 'nullable', Rule::unique('profiles')->ignore($profile_id)],
            'discord_username' => ['string', 'nullable', Rule::unique('profiles')->ignore($profile_id)],
        ];
    }
}
