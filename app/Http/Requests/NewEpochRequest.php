<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewEpochRequest extends FormRequest
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
            'start_time' => !empty($data['start_time']) ? $data['start_time']:'00:00',
            'days' => !empty($data['days']) ? $data['days']:null,
            'repeat' => !empty($data['repeat']) ? $data['repeat']:0,
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
            'start_time' => 'required|date_format:G:i',
            'repeat' => 'required|min:0|max:2',
            'days' => 'required|min:1|max:100',
            'grant' => 'numeric|max:1000000000'
        ];
    }
}
