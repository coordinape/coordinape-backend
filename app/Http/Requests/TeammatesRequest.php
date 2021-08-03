<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeammatesRequest extends FormRequest
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
            'teammates' => $data['teammates']
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ['teammates.*'=> 'integer'];
    }
}
