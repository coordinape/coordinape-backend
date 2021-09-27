<?php

namespace App\Http\Requests;

use App\Models\Circle;
use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateCircleRequest extends FormRequest
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
    protected function prepareForValidation()
    {
        $data = json_decode($this->get('data'), true);
        $this->merge([
            'user_name' => !empty($data['user_name']) ? $data['user_name'] : null,
            'address' => !empty($data['address']) ? strtolower($data['address']) : null,
            'circle_name' => !empty($data['circle_name']) ? $data['circle_name'] : null
        ]);

        if (array_key_exists('protocol_id', $data) &&
            $data['protocol_id']) {

            $this->merge([
                'protocol_id' => $data['protocol_id']
            ]);
        } else if (array_key_exists('protocol_name', $data) &&
            $data['protocol_name']
        ) {
            $this->merge([
                'protocol_name' => $data['protocol_name']
            ]);
        }
    }

    public function rules()
    {
        $addressValidations = ['required', 'string', 'size:42'];
        if ($this->protocol_id) {
            $addressValidations[] = function ($attribute, $value, $fail) {
                $protocol = Protocol::find($this->protocol_id);
                if ($protocol) {
                    $circles = Circle::where('protocol_id', $this->protocol_id)->pluck('id')->toArray();
                    if (count($circles)) {
                        $exists = User::byAddress($value)->where('role', 1)->whereIn('circle_id', $circles)->exists();
                        if (!$exists) {
                            return $fail('Address is not an admin of any circles under this protocol.');
                        }
                    }
                    return true;
                }
                return $fail('The selected protocol id is invalid.');
            };
        }
        return [
            'user_name' => 'required|string|max:255',
            'address' => $addressValidations,
            'circle_name' => 'required|string|max:255',
            'protocol_name' => 'required_without:protocol_id|string|max:255',
            'protocol_id' => 'required_without:protocol_name|integer|exists:protocols,id',
            'uxresearch_json' => 'nullable|json'
        ];
    }
}
