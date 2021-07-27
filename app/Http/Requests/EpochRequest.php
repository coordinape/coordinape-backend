<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EpochRequest extends FormRequest
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

    protected function prepareForValidation() {

        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'start_date' => !empty($data['start_date']) ? $data['start_date']:null,
            'end_date' => !empty($data['end_date']) ? $data['end_date']:null,
            'grant' => !empty($data['grant']) ? $data['grant']:0
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'grant' => 'numeric|max:1000000000'
        ];
    }
}
