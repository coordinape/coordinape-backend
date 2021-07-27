<?php

namespace App\Http\Requests;

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
       return true;
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $circle_id = $this->route('circle_id');
        $this->merge([
            'data' => $data,
            'name' => !empty($data['name']) ? $data['name']:null,
            'circle_id' => $circle_id,
            'non_receiver' => !empty($data['non_receiver']) ? $data['non_receiver']:0,
            'bio' => !empty($data['bio']) ? $data['bio']:null,
            'epoch_first_visit' => !empty($data['epoch_first_visit']) ? $data['epoch_first_visit']:0
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
            'name' => 'required|string|max:255',
            'circle_id' => 'required|integer|exists:circles,id',
            'non_receiver' => 'integer|min:0|max:1|required'
        ];
    }
}
