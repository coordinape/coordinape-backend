<?php

namespace App\Http\Requests;

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
       return true;
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'name' => !empty($data['name']) ? $data['name']:null,
            'token_name' => !empty($data['token_name']) ? $data['token_name']:null,
            'team_sel_text' => !empty($data['team_sel_text']) ? $data['team_sel_text']:null,
            'alloc_text' => !empty($data['alloc_text']) ? $data['alloc_text']:null,
            'vouching'  => !empty($data['vouching']) ? $data['vouching']:0,
            'min_vouches'  => !empty($data['min_vouches']) ? $data['min_vouches']:3,
            'nomination_days_limit' => !empty($data['nomination_days_limit']) ? $data['nomination_days_limit']:14,
            'vouching_text'  => !empty($data['vouching_text']) ? $data['vouching_text']:'',
            'team_selection'  => !empty($data['team_selection']) ? $data['team_selection']:1,
            'default_opt_in'  => !empty($data['default_opt_in']) ? $data['default_opt_in']:0,
        ]);

        if(array_key_exists('discord_webhook', $data)) {
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

            'data' => 'required',
            'name' => 'required|string|max:255',
            'token_name' => 'required|string|max:255',
            'vouching' => 'integer|min:0|max:1',
            'min_vouches' => 'integer|min:1',
            'nomination_days_limit' => 'integer|min:1',
            'vouching_text' => 'string|max:5000',
            'alloc_text' => 'string|max:5000',
            'team_selection' => 'integer|min:0|max:1',
            'default_opt_in' => 'integer|min:0|max:1',
            'discord_webhook' => 'url'
        ];
    }
}
