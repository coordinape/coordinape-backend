<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUserCreateRequest extends FormRequest
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
            'users' => ['required', 'array', 'min:1'],
            'users.*.address' => ['required', 'string', 'size:42', 'distinct',
                // validate id is unique within circle
                Rule::unique('users')->withoutTrashed()->where(function ($query) use ($circle_id) {
                    return $query->where('circle_id', $circle_id);
                })],
            'users.*.name' => ['required', 'string', 'max:255'],
            'users.*.non_giver' => 'integer|min:0|max:1|nullable',
            'users.*.fixed_non_receiver' => 'integer|min:0|max:1|nullable',
            'users.*.starting_tokens' => 'integer|max:1000000|nullable',
            'users.*.role' => 'integer|min:0|max:1|nullable'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge($this->sanitizeInput());

    }

    private function sanitizeInput(): array
    {
        $users = $this->get('users');
        $address_array = [];
        if ($users) {
            $circle_id = $this->route('circle_id');
            $allowed_fields = ['address', 'name', 'non_giver', 'fixed_non_receiver', 'starting_tokens', 'role'];
            foreach ($users as $idx => $user) {
                $users[$idx] = array_filter($user, function ($key) use ($allowed_fields) {
                    return in_array($key, $allowed_fields);
                }
                    , ARRAY_FILTER_USE_KEY);
                $users[$idx]['circle_id'] = $circle_id;
                if (!empty($user['address'])) {
                    $lcAddress = strtolower($user['address']);
                    $users[$idx]['address'] = $lcAddress;
                    $address_array[] = $lcAddress;
                }
            }
        }
        return compact('users', 'address_array');
    }
}
