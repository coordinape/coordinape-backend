<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VouchRequest extends FormRequest
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
            'nominee_id' => !empty($data['nominee_id']) ? $data['nominee_id']:null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user_id = $this->user->id;
        $nominee_id = $this->nominee_id;

        return [
            'nominee_id' => ['required', 'integer', Rule::unique('vouches')
                ->where(function ($query) use ($user_id, $nominee_id) {
                    return $query->where('voucher_id', $user_id)->where('nominee_id', $nominee_id);
                })]
        ];
    }
}
