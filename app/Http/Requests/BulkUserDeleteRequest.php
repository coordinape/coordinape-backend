<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUserDeleteRequest extends FormRequest
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
        $circle_id = $this->route('circle_id');
        return [
            "users" => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'integer', 'distinct',
                //validate id exists as a user in the circle
                Rule::exists('users', 'id')->withoutTrashed()->where(function ($query) use ($circle_id) {
                    return $query->where('circle_id', $circle_id);
                })],
        ];
    }
}
