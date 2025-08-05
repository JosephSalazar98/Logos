<?php

namespace App\Helpers;

class Logger
{
    protected static string $logFile = '';

    protected static function init(string $filename = 'gpt.log'): void
    {
        if (self::$logFile === '') {
            self::$logFile = dirname(__DIR__, 2) . "/storage/logs/{$filename}";
        }
    }

    public static function info(mixed $message, string $filename = 'gpt.log'): void
    {
        self::init($filename);
        self::writeLog('INFO', $message);
    }

    public static function error(string $message, string $filename = 'gpt.log'): void
    {
        self::init($filename);
        self::writeLog('ERROR', $message);
    }

    public static function debug(string $message, string $filename = 'gpt.log'): void
    {
        self::init($filename);
        self::writeLog('DEBUG', $message);
    }

    protected static function writeLog(string $level, mixed $message): void
    {
        $timestamp = date('Y-m-d H:i:s');

        // Force any type into string safely
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $message = (string) $message;
        }

        $line = "[{$timestamp}] {$level}: {$message}\n";
        file_put_contents(self::$logFile, $line, FILE_APPEND);
    }
}
