<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class NewGiftRequest extends FormRequest
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
        $this->merge([
            'gifts' => json_decode($this->get('data'), true)
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'gifts.*.recipient_id' => 'required|integer',
            'gifts.*.tokens' => 'required|integer|min:0'
        ];

        $gifts = $this->gifts;
        if(!$gifts)
            throw new ConflictHttpException('data cannot be null');

        $sum = array_reduce($gifts, function($carry, $item)
        {
            return $carry + $item['tokens'];
        });

        $user = $this->user;
        if(!$user)
            throw new ConflictHttpException('User cannot be found');

        if($sum > $user->starting_tokens) {
            throw new ConflictHttpException('Sum of tokens is more than '. $user->starting_tokens);
        }

        $activeEpoch = $user->circle->epoches()->isActiveDate()->first();
        if(!$activeEpoch) {
            throw new ConflictHttpException('Currently not in an active Epoch');
        }

        return $rules;
    }
}
