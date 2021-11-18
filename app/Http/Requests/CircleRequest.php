<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CircleRequest extends FormRequest
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
        $this->merge([
            'name' => !empty($data['name']) ? $data['name']:null,
            'token_name' => !empty($data['token_name']) ? $data['token_name']:null,
            'team_sel_text' => !empty($data['team_sel_text']) ? $data['team_sel_text']:null,
            'alloc_text' => !empty($data['alloc_text']) ? $data['alloc_text']:null,
            'vouching'  => !empty($data['vouching']) ? $data['vouching']:0,
            'min_vouches'  => !empty($data['min_vouches']) ? $data['min_vouches']:3,
            'min_vouches_percent'  => !empty($data['min_vouches_percent']) ? $data['min_vouches_percent']:50,
            'calculate_vouching_percent'  => !empty($data['calculate_vouching_percent']) ? $data['calculate_vouching_percent']:0,
            'nomination_days_limit' => !empty($data['nomination_days_limit']) ? $data['nomination_days_limit']:14,
            'vouching_text'  => !empty($data['vouching_text']) ? $data['vouching_text']:'',
            'team_selection'  => !empty($data['team_selection']) ? $data['team_selection']:0,
            'default_opt_in'  => !empty($data['default_opt_in']) ? $data['default_opt_in']:0,
            'only_giver_vouch'  => !empty($data['only_giver_vouch']) ? $data['only_giver_vouch']:0,
        ]);

        if(array_key_exists('discord_webhook', $data) &&
            array_key_exists('update_webhook', $data) &&
            $data['update_webhook'] == 1
        ) {
            $this->merge([
                'discord_webhook' => $data['discord_webhook']
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'token_name' => 'required|string|max:255',
            'vouching' => 'integer|min:0|max:1',
            'min_vouches' => 'integer|min:1',
            'nomination_days_limit' => 'integer|min:1',
            'vouching_text' => 'string|max:5000',
            'alloc_text' => 'string|max:5000',
            'team_selection' => 'integer|min:0|max:1',
            'default_opt_in' => 'integer|min:0|max:1',
            'discord_webhook' => 'url',
            'only_giver_vouch' => 'integer|min:0|max:1',
            'min_vouches_percent' => 'decimal|min:1|max:100',
            'calculate_vouching_percent' => 'integer|min:0|max:1',
        ];
    }
}
