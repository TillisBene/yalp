<?php

namespace utils;

class CookieGenerator {
    /**
     * Creates a new cookie with specified parameters
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expires Expiration time in seconds from now (default 30 days)
     * @param string $path Cookie path (default '/')
     * @param bool $secure HTTPS only (default true)
     * @param bool $httpOnly HTTP only flag (default true)
     * @return bool Success status
     */
    public static function set(
        string $name,
        string $value,
        int $expires = 2592000, // 30 days
        string $path = '/',
        bool $secure = true,
        bool $httpOnly = true
    ): bool {
        return setcookie(
            $name,
            $value,
            [
                'expires' => time() + $expires,
                'path' => $path,
                'secure' => $secure,
                'httponly' => $httpOnly,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Removes a cookie by setting its expiration to the past
     * 
     * @param string $name Cookie name
     * @param string $path Cookie path (default '/')
     * @return bool Success status
     */
    public static function remove(string $name, string $path = '/'): bool {
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path
        ]);
    }
}