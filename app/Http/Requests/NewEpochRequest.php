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
            'start_date' => !empty($data['start_date']) ? $data['start_date']:null,
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
            'start_date' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            'repeat' => 'required|integer|min:0|max:2',
            'days' => ['required','integer','min:1','max:100',
                function ($attribute, $value, $fail) {
                    if($value > 7 && $this->repeat == 1)
                        return $fail('You cannot have more than 7 days length for a weekly repeating epoch.');
                    if($value > 28 && $this->repeat == 2)
                        return $fail('You cannot have more than 28 days length for a monthly repeating epoch.');
            }],
            'grant' => 'numeric|max:1000000000',
        ];
    }
}
