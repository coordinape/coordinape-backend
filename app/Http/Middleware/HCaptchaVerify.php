<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class HCaptchaVerify
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
        $client = new Client(['headers' => ['content-type' => 'application/x-www-form-urlencoded']]);
        $response = $client->post('https://hcaptcha.com/siteverify',
            [
                'form_params' => [
                    "response" => $request->get('captcha_token'),
                    "secret" => config('services.hcaptcha.secret'),
                    "sitekey" => config('services.hcaptcha.sitekey')
                ]]
        );

        $ret = json_decode((string)$response->getBody());
        if (!$ret->success)
            abort(403, 'Captcha is invalid');

        return $next($request);
    }
}
