<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUserRestoreRequest extends FormRequest
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
                //validate id exists as a deleted user in circle
                Rule::exists('users', 'id')->where(function ($query) use ($circle_id) {
                    return $query->where('circle_id', $circle_id)->whereNotNull('deleted_at');
                })],
            'addresses.*' => ['distinct', 'string', 'size:42',
                //validate restored user doesn't conflict with existing user addresses
                Rule::unique('users', 'address')->withoutTrashed()->where(function ($query) use ($circle_id) {
                    return $query->where('circle_id', $circle_id);
                })],
        ];
    }

    protected function prepareForValidation()
    {
        $users = $this->get('users');
        $restoring_addresses = [];
        if ($users && is_array($users)) {
            $restoring_addresses = User::onlyTrashed()->whereIn('id', $users)->where('circle_id', $this->route('circle_id'))->pluck('address')->toArray();
        }
        $this->merge(['addresses' => $restoring_addresses]);
    }
}
