<?php
declare(strict_types=1);

namespace App\helpers;

class Crypto
{
    /**
     * @throws \SodiumException
     */
    static function encrypt($data): string
    {
        return base64_encode(sodium_crypto_box_seal($data, base64_decode($_SERVER['PUBLIC_KEY'])));
    }

    /**
     * @throws \SodiumException
     */
    static function decrypt($data): string
    {
        return sodium_crypto_box_seal_open(base64_decode($data), base64_decode($_SERVER['PRIVATE_KEY']));
    }
}