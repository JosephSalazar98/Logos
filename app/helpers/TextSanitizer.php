<?php

namespace App\Helpers;

class TextSanitizer
{
    public static function cleanGptReply(string $text): string
    {
        // Remove markdown symbols and special punctuation
        $text = str_replace(
            ['**', '__', '*', '_', '`', '—', '–'],
            '',
            $text
        );

        // Remove curly quotes and straight quotes
        $text = str_replace(
            ['“', '”', '‘', '’', '"', "'"],
            '',
            $text
        );

        // Normalize line endings to \n
        $text = preg_replace("/\r\n|\r/", "\n", $text);

        // Replace multiple spaces (but not newlines) with a single space
        $text = preg_replace('/ {2,}/', ' ', $text);

        // Trim spaces at start and end of each line, but keep line breaks
        $text = preg_replace('/^[ \t]+|[ \t]+$/m', '', $text);

        // Remove extra blank lines (more than 2 in a row)
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        // Final trim
        return trim($text);
    }
}
