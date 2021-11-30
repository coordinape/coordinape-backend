<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use App\Models\User;
use Closure;
use Error;
use Illuminate\Http\Request;

class VerifyLoginSignature
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
        $signature = $request->get('signature');
        $address = $request->get('address');
        $hash = $request->get('hash');
        $data = $request->get('data');

        $signature_timestamp = str_replace('Login to Coordinape', '', $data);
    
        if (!ctype_digit(trim(strval($signature_timestamp))))
           abort(401, 'Login signature is missing timestamp');

        $ts1 = (int)$signature_timestamp;
        $ts2 = time();
        $seconds_diff = $ts2 - $ts1;

        if ($seconds_diff > 300) // 5 minutes
            abort(401, 'Login signature is too old');
        
        $valid = false;

        try {
            $valid = Utils::validateSignature($address, $data, $signature, $hash);
        } catch (Error) {}

        if (!$valid)
            abort(401, 'Login signature is not valid');

        return $next($request);
    }
}
