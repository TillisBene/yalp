<?php

/**
 * 
 * Utils for Sessionmanagement
 * 
 */
namespace utils;

final class SessionManager
{
    public static function createSession()
    {
        if(!isset($_SESSION)){
            session_start();
        }
    }

    public static function resetSession()
    {
        if(isset($_SESSION)){
            session_reset();
        }
    }

    public static function closeSession()
    {
        if(isset($_SESSION)){
            session_destroy();
        }
    }
    public static function isAuthenticated(): bool
    {
        if(!isset($_SESSION)) {
            return false;
        }
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
    public static function addToSession(array $data): void
    {
        if(!isset($_SESSION)) {
            return;
        }
        foreach ($data as $key => $value) {
            if (str_contains($key, '.')) {
                $keys = explode('.', $key);
                $current = &$_SESSION;
                foreach ($keys as $k) {
                    if (!isset($current[$k])) {
                        $current[$k] = [];
                    }
                    $current = &$current[$k];
                }
                $current = $value;
            } else {
                $_SESSION[$key] = $value;
            }
        }
    }
}

