<?php

namespace App\Http\Middleware;

use App\Helper\Utils;
use Closure;
use Illuminate\Http\Request;

class VerifyUserToken
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
        if (!$existing_user = Utils::getCircleUserFromRequest($request, $circle_id)) {
            abort(403, 'You are not authorized to perform this action');
        }
        if ($circle_id) {
            $request->merge([
                'user' => $existing_user,
            ]);
        }

        return $next($request);
    }
}
