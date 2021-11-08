<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifyCircleAdmin
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
        $circle_id = $request->route('circle_id');
        if (!$admin_user = Utils::getCircleUserFromRequest($request, $circle_id, true)) {
            abort(403, 'You are not authorized to perform this action');
        }

        $address = $request->route('address');
        if ($address) {
            $updating_user = User::byAddress($address)->where('circle_id', $circle_id)->first();
            $user = $updating_user;
        } else {
            $user = $admin_user;
        }
        $request->merge([
            'admin_user' => $admin_user,
            'user' => $user,
        ]);
        return $next($request);
    }
}
