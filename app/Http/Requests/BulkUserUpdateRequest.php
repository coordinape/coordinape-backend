<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUserUpdateRequest extends FormRequest
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
        $user_id = $this->input('users.*.id');
        $circle_id = $this->route('circle_id');
        return [
            'users' => ['required', 'array', 'min:1'],
            'users.*.id' => ['required', 'integer',
                // validate id exists as a user in circle
                Rule::exists('users')->where(function ($query) use ($circle_id) {
                    return $query->where('circle_id', $circle_id)->whereNull('deleted_at');
                })],
            'users.*.address' => ['nullable', 'string', 'size:42', 'distinct',
                // validate address is unique within circle
                Rule::unique('users')->where(function ($query) use ($circle_id, $user_id) {
                    return $query->where('circle_id', $circle_id)->whereNotIn('id', $user_id)->whereNull('deleted_at');
                })],
            'users.*.name' => ['nullable', 'string', 'max:255'],
            'users.*.non_giver' => 'integer|min:0|max:1|nullable',
            'users.*.fixed_non_receiver' => 'integer|min:0|max:1',
            'users.*.starting_tokens' => 'integer|min:0|max:1000000',
            'users.*.role' => 'integer|min:0|max:1|nullable'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge($this->sanitizeInput());
    }

    private function sanitizeInput(): array
    {
        $users_values = $this->get('users');
        $admin_user_id = $this->get('admin_user')->id;
        $address_array = [];
        $id_array = [];
        $users = [];
        if ($users_values) {
            $circle_id = $this->route('circle_id');
            $allowed_fields = ['id', 'address', 'name', 'non_giver', 'fixed_non_receiver', 'starting_tokens', 'role'];
            $id_array = array_map(function ($u) {
                return !empty($u['id']) ? $u['id'] : -1;
            }, $users_values);
            $users_current_data = User::where('circle_id', $circle_id)->whereIn('id', $id_array)->get()->keyBy('id');
            foreach ($users_values as $users_value) {
                $user_id = !empty($users_value['id']) ? $users_value['id'] : null;
                if ($user_id) {
                    $user = [];
                    if ($users_current_data->has($user_id)) {
                        foreach ($allowed_fields as $allowed_field) {
                            $user[$allowed_field] = !empty($users_value[$allowed_field]) ? $users_value[$allowed_field] : $users_current_data[$user_id]->{$allowed_field};
                        }
                        $user['address'] = strtolower($user['address']);
                        $address_array[] = $user['address'];
                        if ($user_id == $admin_user_id) {
                            // prevents admin user from removing his own admin role
                            $user['role'] = config('enums.user_types.admin');
                        }
                    } else {
                        $user['id'] = $user_id;
                    }
                    $user['circle_id'] = $circle_id;
                    $users[] = $user;
                }
            }
        }
        return compact('users', 'address_array', 'id_array');
    }
}
