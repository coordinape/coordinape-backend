<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifySignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $data = $request->get('data');
        $signature = $request->get('signature');
        $address  = $request->get('address');
        $hash = $request->get('hash');
        if($request->route('circle_id'))
        {
            $circle_id = $request->route('circle_id');
            $existing_user =  User::byAddress($address)->where('circle_id', $circle_id)->first();
            $request->merge([
                'user' => $existing_user,
            ]);
            if(!$existing_user)
                abort(403, 'You are not authorized to perform this action');
        }

        if(!Utils::validateSignature($address, $data, $signature, $hash))
            abort(403, 'You are not authorized to perform this action');

        return $next($request);
    }
}
