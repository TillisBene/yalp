<?php

/**
 * 
 * Utils for Sessionmanagement
 * 
 */
namespace utils;

use database\Connection;

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

    public static function getFromSession(string $key)
    {
        if(!isset($_SESSION)) {
            return null;
        }
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $current = $_SESSION;
            foreach ($keys as $k) {
                if (!isset($current[$k])) {
                    return null;
                }
                $current = $current[$k];
            }
            return $current;
        }
        return $_SESSION[$key] ?? null;
    }

    public static function removeFromSession(string $key): void
    {
        if(!isset($_SESSION)) {
            return;
        }
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $current = &$_SESSION;
            foreach ($keys as $k) {
                if (!isset($current[$k])) {
                    return;
                }
                $current = &$current[$k];
            }
            unset($current);
        } else {
            unset($_SESSION[$key]);
        }
    }

    public static function changeKeyInSession(string $oldKey, string $newKey): void
    {
        if(!isset($_SESSION)) {
            return;
        }
        if (str_contains($oldKey, '.')) {
            $keys = explode('.', $oldKey);
            $current = &$_SESSION;
            foreach ($keys as $k) {
                if (!isset($current[$k])) {
                    return;
                }
                $current = &$current[$k];
            }
            $value = $current;
            unset($current);
            self::addToSession([$newKey => $value]);
        } else {
            $value = $_SESSION[$oldKey];
            unset($_SESSION[$oldKey]);
            self::addToSession([$newKey => $value]);
        }
    }

    public static function changeValueInSession(string $key, $value): void
    {
        if(!isset($_SESSION)) {
            return;
        }
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $current = &$_SESSION;
            foreach ($keys as $k) {
                if (!isset($current[$k])) {
                    return;
                }
                $current = &$current[$k];
            }
            $current = $value;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    public static function existsInSession(string $key): bool
    {
        if(!isset($_SESSION)) {
            return false;
        }
        return isset($_SESSION[$key]);
    }
    public static function isActive(): bool
    {
        if(!isset($_SESSION)) {
            return false;
        }
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function isAuthenticated(): bool
    {
        //error_log('SESSION CHECK: ' . session_id());
        //error_log('SESSION DATA: ' . print_r($_SESSION, true));
        
        if(!isset($_SESSION)) {
            error_log('Session not set');
            return false;
        }
        
        $userId = self::getFromSession('user_id');
        error_log('USER ID FROM SESSION: ' . ($userId ?? 'null'));
        
        //$dbSession = Connection::getInstance()->get('users', 'current_session', [
        //    'user_id' => $userId
        //]);
        
        error_log('DB SESSION: ' . ($dbSession ?? 'null'));
        error_log('CURRENT SESSION: ' . session_id());
        
        //if (!isset($dbSession) || $dbSession !== session_id()) {
        //    error_log('Session mismatch or not found');
        //    self::closeSession();
        //    return false;
        //}
    
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
}

