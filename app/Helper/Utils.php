<?php

namespace App\Helper;


use Elliptic\EC;
use Illuminate\Support\Facades\Cache;
use kornrunner\Keccak;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

class Utils
{

    const VALID = '0x1626ba7e';
    public static function validateSignature(string $address, string $message, string $signature, string $hash = null) {

        if($hash) {
            return self::validateContractSignature($address, $hash, $signature);
        }

        return $address == strtolower(self::personalEcRecover( $message,  $signature)) ||
                $address == strtolower(self::personalEcRecover( $message,  $signature, false));
    }

    public static function validateContractSignature(string $address, string $hash, string $signature): bool {
        $contractABI = '[{
            "inputs": [{
                "internalType": "bytes32",
                "name": "_message",
                "type": "bytes32"
            }, {
                "internalType": "bytes",
                "name": "_signature",
                "type": "bytes"
            }],
            "name": "isValidSignature",
            "outputs": [{
                "internalType": "bytes4",
                "name": "",
                "type": "bytes4"
            }],
            "payable": false,
            "stateMutability": "view",
            "type": "function"
        }]';

        $web3 = new Web3(new HttpProvider(new HttpRequestManager(env('INFURA_API'),5)));
        $contract = new Contract($web3->provider, $contractABI);
        $valid = false;
        $contract->at($address)->call('isValidSignature',$hash,$signature, null , function($err,$ret) use (&$valid) {
            if($ret && count($ret))
            {
                $valid = $ret[0] == self::VALID;
            }
        });

        return $valid;
    }

    public static function personalEcRecover(string $message, string $signature, $withPrefix = true)
    {
        $message = $withPrefix ? self::personalSignAddHeader($message) : $message;
        $message_hash =  '0x' . Keccak::hash($message, 256);
        $address = self::phpEcRecover($message_hash, $signature);
        return $address;
    }

    public static function phpEcRecover(string $message_hash, string $signature)
    {
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
        if(config('cache.default') == 'redis')
            return Cache::tags($tags)->remember($fullUrl, $minutes, $callback);

        return Cache::remember($fullUrl, $minutes, $callback);

    }

    public static function purgeCache($tag)
    {
        if(config('cache.default') == 'redis')
            Cache::tags([$tag,'default'])->flush();
    }

    public static function cleanStr($str): string {

        return str_replace(array(':', '-', '/', '*','_','`'), ' ', $str);

    }
}
