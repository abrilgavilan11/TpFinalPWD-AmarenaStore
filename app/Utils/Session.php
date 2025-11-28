<?php

namespace App\Utils;

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function init()
    {
        self::start();
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
        error_log("[v0] Session SET - Key: {$key}, Count: " . (is_array($value) ? count($value) : 'N/A'));
    }

    public static function get($key, $default = null)
    {
        self::start();
        $value = $_SESSION[$key] ?? $default;
        $hasValue = isset($_SESSION[$key]) ? 'YES' : 'NO';
        $count = is_array($value) ? count($value) : 'N/A';
        error_log("[v0] Session GET - Key: {$key}, Has value: {$hasValue}, Count: {$count}");
        return $value;
    }

    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        self::start();
        session_destroy();
    }

    public static function flash($key, $value = null)
    {
        self::start();
        if ($value === null) {
            $message = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $message;
        }
        $_SESSION['_flash'][$key] = $value;
    }
}
