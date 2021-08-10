<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifyAdminSignature
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
        $circle_id = $request->route('circle_id');
        $admin_user = User::byAddress($address)->isAdmin()->where('circle_id', $circle_id)->first();
        if($request->route('address')) {
            $updating_user =  User::byAddress($request->route('address'))->where('circle_id', $circle_id)->first();
            $user = $updating_user;
        } else {
            $user = $admin_user;
        }
        $request->merge([
            'admin_user' =>$admin_user,
            'user' => $user,
        ]);
        if(!$admin_user || !Utils::validateSignature($address, $data, $signature, $hash))
            abort(403, 'You are not authorized to perform this action');

        return $next($request);
    }
}
