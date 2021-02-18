<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Models\User;

class GiftRequest extends FormRequest
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
        $rules = [
            'circle_id' => 'required|integer',
            'gifts' => 'required',
            'gifts.*.recipient_address' => 'required|string|size:42',
            'gifts.*.tokens' => 'required|integer|max:100'
        ];

        $gifts = $this->get('gifts');
        if(!$gifts)
            throw new ConflictHttpException('Gift cannot be null');

//        if(!$this->address)
//            throw new ConflictHttpException('Address cannot be null');

        $sum = array_reduce($gifts, function($carry, $item)
        {
            return $carry + $item['tokens'];
        });

//        $user = User::byAddress($this->address)->first();
//        if(!$user)
//            throw new ConflictHttpException('User cannot be found');
//
        if($sum > 100) {
            throw new ConflictHttpException('Sum of tokens is more than 100');
        }

        return $rules;
    }
}
