<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use Closure;
use Illuminate\Http\Request;

class VerifySignatureOnly
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
        if(!Utils::validateSignature($address, $data, $signature, $hash))
            abort(403, 'You are not authorized to perform this action');

        return $next($request);
    }
}
