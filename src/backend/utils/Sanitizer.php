<?php

namespace utils;

class Sanitizer
{
    /**
     * Sanitize a string by removing special characters and trimming
     */
    public static function cleanString(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        return trim(strip_tags($input));
    }

    /**
     * Sanitize and validate email address
     */
    public static function email(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Sanitize and validate integer
     */
    public static function integer($input, int $min = null, int $max = null): ?int
    {
        $value = filter_var($input, FILTER_VALIDATE_INT);
        if ($value === false) {
            return null;
        }
        if ($min !== null && $value < $min) {
            return null;
        }
        if ($max !== null && $value > $max) {
            return null;
        }
        return $value;
    }

    /**
     * Sanitize and validate float
     */
    public static function float($input, float $min = null, float $max = null): ?float
    {
        $value = filter_var($input, FILTER_VALIDATE_FLOAT);
        if ($value === false) {
            return null;
        }
        if ($min !== null && $value < $min) {
            return null;
        }
        if ($max !== null && $value > $max) {
            return null;
        }
        return $value;
    }

    /**
     * Sanitize string for URL slug
     */
    public static function slug(string $input): string
    {
        $slug = strtolower($input);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
    /**
     * Validate string length is within bounds
     */
    public static function length(?string $input, int $min = null, int $max = null): ?string
    {
        if ($input === null) {
            return null;
        }
        $length = strlen($input);
        if ($min !== null && $length < $min) {
            return null;
        }
        if ($max !== null && $length > $max) {
            return null;
        }
        return $input;
    }
}