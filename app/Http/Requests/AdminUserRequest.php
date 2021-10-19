<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
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
            'address' => !empty($data['address']) ? strtolower($data['address']):null,
            'non_giver'  => !empty($data['non_giver']) ? $data['non_giver']:0,
            'fixed_non_receiver'  => !empty($data['fixed_non_receiver']) ? $data['fixed_non_receiver']:0,
            'starting_tokens'  => !empty($data['starting_tokens']) ? $data['starting_tokens']:100,
            'non_receiver' => !empty($data['non_receiver']) ? $data['non_receiver']:0,
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
            'address' => ['required', 'string', 'size:42',Rule::unique('users')->ignore($this->user->id)->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id)->whereNull('deleted_at');
            })],
            'starting_tokens' => 'integer|max:1000000',
            'non_giver' => 'integer|min:0|max:1|required',
            'fixed_non_receiver' => 'integer|min:0|max:1|required',
            'non_receiver' => 'integer|min:0|max:1|required',
            'role' => 'integer|min:0|max:1|required'
        ];
    }
}
