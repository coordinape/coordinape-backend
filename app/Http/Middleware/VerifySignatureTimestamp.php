<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifySignatureTimestamp
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $data = $request->get('data');
        $signature = $request->get('signature');
        $address = $request->get('address');
        if ($data && $signature && $address) {
            $signature_timestamp = str_replace('Login to Coordinape', '', $data);
            if ((ctype_digit(trim(strval($signature_timestamp))))) {
                $ts1 = (int)$signature_timestamp;
                $ts2 = time();
                $seconds_diff = $ts2 - $ts1;
                if ($seconds_diff <= 120) {
                    $hash = $request->get('hash');
                    if ($request->route('circle_id')) {
                        $circle_id = $request->route('circle_id');
                        $existing_user = User::byAddress($address)->where('circle_id', $circle_id)->first();
                        $request->merge([
                            'user' => $existing_user,
                        ]);
                        if (!$existing_user)
                            abort(403, 'You are not authorized to perform this action');
                    }

                    if (Utils::validateSignature($address, $data, $signature, $hash))
                        return $next($request);
                }
            }
        }
        abort(403, 'You are not authorized to perform this action');
    }
}
