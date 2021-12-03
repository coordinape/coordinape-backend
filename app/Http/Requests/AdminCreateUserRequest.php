<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'name' => !empty($data['name']) ? $data['name']:null,
            'address' => !empty($data['address']) ? strtolower($data['address']):null,
            'non_giver'  => !empty($data['non_giver']) ? $data['non_giver']:false,
            'starting_tokens'  => !empty($data['starting_tokens']) ? $data['starting_tokens']:100,
            'give_token_remaining'  => !empty($data['starting_tokens']) ? $data['starting_tokens']:100,
            'fixed_non_receiver'  => !empty($data['fixed_non_receiver']) ? $data['fixed_non_receiver']:false,
            'non_receiver'  => !empty($data['non_receiver']) ? $data['non_receiver']:false,
            'role'  => !empty($data['role']) ? $data['role']:0,
        ]);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $circle_id = $this->circle_id;
        return [
            'name' => 'required|string|max:255',
            'address' => ['required', 'string', 'size:42', Rule::unique('users')->withoutTrashed()->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id);
            })],
            'non_giver' => 'boolean|required',
            'fixed_non_receiver' => 'boolean|required',
            'non_receiver' => 'boolean|required',
            'starting_tokens' => 'integer|max:1000000',
            'role' => 'integer|min:0|max:1|required'
        ];
    }
}
