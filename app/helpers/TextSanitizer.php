<?php

namespace App\Helpers;

class TextSanitizer
{
    /**
     * Limpia texto generado por GPT removiendo formateos no deseados.
     */
    public static function cleanGptReply(string $text): string
    {
        // Quitar markdown y puntuación especial
        $text = str_replace(
            ['**', '__', '*', '_', '`', '—', '–'],
            '',
            $text
        );

        // Eliminar comillas tipográficas y estándar
        $text = str_replace(
            ['“', '”', '‘', '’', '"', "'"],
            '',
            $text
        );

        // Eliminar múltiples espacios
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }
}
