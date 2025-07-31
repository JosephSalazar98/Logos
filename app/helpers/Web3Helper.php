<?php

namespace App\Helpers;

use kornrunner\Keccak;

class Web3Helper
{
    public static function base58ToBytes(string $base58): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $baseCount = strlen($alphabet);
        $decoded = '0';

        for ($i = 0; $i < strlen($base58); $i++) {
            $char = $base58[$i];
            $index = strpos($alphabet, $char);
            $decoded = bcmul($decoded, (string)$baseCount);
            $decoded = bcadd($decoded, (string)$index);
        }

        $hex = '';
        while (bccomp($decoded, '0') > 0) {
            $byte = bcmod($decoded, '256');
            $decoded = bcdiv($decoded, '256', 0);
            $hex = str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . $hex;
        }

        $nPad = 0;
        for ($i = 0; $i < strlen($base58) && $base58[$i] === '1'; $i++) {
            $nPad++;
        }

        return hex2bin(str_repeat('00', $nPad) . $hex);
    }
}
