<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CsvRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'circle_id' => 'required|integer|exists:circles,id',
            'epoch' => 'required_if:epoch_id,=,null|integer',
            'epoch_id'=>'required_if:epoch,=,null|integer',
        ];
    }
}
