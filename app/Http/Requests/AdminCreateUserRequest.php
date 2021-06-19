<?php

namespace App\Http\Requests;

use App\Helper\Utils;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCreateUserRequest extends FormRequest
{
    public function authorize()
    {
        $data = $this->get('data');
        $signature = $this->get('signature');
        $address  = $this->get('address');
        $circle_id = $this->route('subdomain');
        if($circle_id) {
            $admin_user = User::byAddress($this->get('address'))->isAdmin()->where('circle_id', $circle_id)->first();
        } else {
            return false;
        }
        $recoveredAddress = Utils::personalEcRecover($data,$signature);
        $this->merge([
            'circle_id' => $circle_id,
            'admin_user' => $admin_user
        ]);
        return $admin_user && strtolower($recoveredAddress)==strtolower($address);
    }

    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'data' => $data,
            'name' => !empty($data['name']) ? $data['name']:null,
            'address' => !empty($data['address']) ? strtolower($data['address']):null,
            'non_giver'  => !empty($data['non_giver']) ? $data['non_giver']:0,
            'starting_tokens'  => !empty($data['starting_tokens']) ? $data['starting_tokens']:100,
            'give_token_remaining'  => !empty($data['starting_tokens']) ? $data['starting_tokens']:100,
            'fixed_non_receiver'  => !empty($data['fixed_non_receiver']) ? $data['fixed_non_receiver']:0

        ]);

        if(!empty($data['role'])) {
            $this->merge(['role' => $data['role']]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $circle_id = $this->circle_id;
        return [
            'data' => 'required',
            'name' => 'required|string|max:255',
            'address' => ['required', 'string', 'size:42', Rule::unique('users')->where(function ($query) use ($circle_id) {
                return $query->where('circle_id', $circle_id)->whereNull('deleted_at');
            })],
            'non_giver' => 'integer|min:0|max:1|required',
            'fixed_non_receiver' => 'integer|min:0|max:1|required',
            'starting_tokens' => 'integer|max:1000000',
            'role' => 'integer|min:0|max:1'
        ];
    }
}
