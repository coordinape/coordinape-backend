<?php

namespace App\Helper;


use Elliptic\EC;
use Illuminate\Support\Facades\Cache;
use kornrunner\Keccak;
use App\Models\Circle;

class Utils
{

    public static function personalEcRecover(string $message, string $signature, $withPrefix = true)
    {
        $message = $withPrefix ? self::personalSignAddHeader($message) : $message;
        $message_hash =  '0x' . Keccak::hash($message, 256);
        $address = self::phpEcRecover($message_hash, $signature);
        return $address;
    }

    public static function phpEcRecover(string $message_hash, string $signature)
    {
        $return = NULL;

        $ec = new EC('secp256k1');

        $sign   = ["r" => substr($signature, 2, 64), "s" => substr($signature, 66, 64)];
        $val = ord(hex2bin(substr($signature, 130, 2)));
        $recid  = ($val - 27) < 0 ? $val:($val - 27);

        $pubKey = $ec->recoverPubKey($message_hash, $sign, $recid);

        $recoveredAddress = "0x" . substr(Keccak::hash(substr(hex2bin($pubKey->encode("hex")), 1), 256), 24);
        return $recoveredAddress;
    }

    public static function personalSignAddHeader($message)
    {
        // MUST be double quotes.
        return "\x19Ethereum Signed Message:\n" . strlen($message) . $message;
    }

    public static function queryCache($request,$callback,$minutes=1,$tags='default')
    {

        $url = $request->url();
        $queryParams = $request->query();

        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";
        if(env('CACHE_DRIVER')=='redis')
            return Cache::tags($tags)->remember($fullUrl, $minutes, $callback);

        return Cache::remember($fullUrl, $minutes, $callback);

    }

    public static function purgeCache($tag)
    {
        if(env('CACHE_DRIVER')=='redis')
            Cache::tags([$tag,'default'])->flush();
    }

    public static function cleanStr($str): string {

        return str_replace(array(':', '-', '/', '*','_','`'), ' ', $str);

    }
}
