<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NomineeRequest extends FormRequest
{
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
            'address' => !empty($data['address']) ? strtolower($data['address']):null,
            'description'  => !empty($data['description']) ? $data['description']:null,
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
            'data' => 'required',
            'name' => 'required|string|max:255',
            'address' => ['required', 'string', 'size:42',Rule::unique('nominees')->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id)->where('ended',0);
            }), Rule::unique('users')->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id)->whereNull('deleted_at');
            })],
            'description' => 'required|string|max:5000',
        ];
    }
}
