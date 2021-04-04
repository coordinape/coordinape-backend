<?php

namespace App\Helper;


use Elliptic\EC;
use kornrunner\Keccak;
use App\Models\Circle;

class Utils
{

    const circleMap = ['yearn'=>1 ];

    public static function personalEcRecover(string $message, string $signature)
    {
        $message_hash =  '0x' . Keccak::hash(self::personalSignAddHeader($message), 256);
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

    public static function getCircleIdByName($name) {

        if(array_key_exists($name,Utils::circleMap))
            return Utils::circleMap[$name];

        $circle = Circle::where('name',$name )->first();
        return $circle ? $circle->id : null;

    }

}
