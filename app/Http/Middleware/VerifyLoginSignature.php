<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use App\Models\User;
use Closure;
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
        $data = "Login to Coordinape";

        if(!$address || !Utils::validateSignature($address, $data, $signature, $hash))
            abort(403, 'Login signature is not valid');

        return $next($request);
    }
}
