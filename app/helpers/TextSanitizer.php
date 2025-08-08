<?php

namespace App\Helpers;

class TextSanitizer
{

    public static function cleanGptReply(string $text): string
    {
        $text = str_replace(
            ['**', '__', '*', '_', '`', '—', '–'],
            '',
            $text
        );

        $text = str_replace(
            ['“', '”', '‘', '’', '"', "'"],
            '',
            $text
        );

        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }
}
