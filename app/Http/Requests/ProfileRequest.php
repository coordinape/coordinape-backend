<?php

namespace App\Http\Requests;

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
        return true;
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $profile = $this->user();
        $this->merge([
            'profile' => $profile,
            'bio' => !empty($data['bio']) ? $data['bio']:null,
            'skills' => !empty($data['skills']) ? $data['skills']:null,
            'github_username' => !empty($data['github_username']) ? $data['github_username']:null,
            'telegram_username' => !empty($data['telegram_username']) ? $data['telegram_username']:null,
            'discord_username' => !empty($data['discord_username']) ? $data['discord_username']:null,
            'twitter_username' => !empty($data['twitter_username']) ? $data['twitter_username']:null,
            'medium_username' => !empty($data['medium_username']) ? $data['medium_username']:null,
            'website' => !empty($data['website']) ?
                        (parse_url($data['website'], PHP_URL_SCHEME) === null ?
                        "https://" . $data['website'] : $data['website']) : null
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $profile_id = $this->profile->id;
        return [
            'telegram_username' => ['string', 'nullable', Rule::unique('profiles')->ignore($profile_id)],
            'discord_username' => ['string', 'nullable', Rule::unique('profiles')->ignore($profile_id)],
            'website' => ['nullable', 'active_url'],
        ];
    }
}
