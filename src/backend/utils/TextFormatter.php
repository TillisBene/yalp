<?php

namespace utils;

class TextFormatter {
    /**
     * Remove all spaces from a string
     */
    public static function removeSpaces(string $text): string {
        return str_replace(' ', '', $text);
    }

    /**
     * Replace spaces with a specific character
     */
    public static function replaceSpaces(string $text, string $replacement = '-'): string {
        return str_replace(' ', $replacement, $text);
    }

    /**
     * Convert text to uppercase
     */
    public static function toUpperCase(string $text): string {
        return strtoupper($text);
    }

    /**
     * Convert text to lowercase
     */
    public static function toLowerCase(string $text): string {
        return strtolower($text);
    }

    /**
     * Check if text contains specific words
     */
    public static function containsWords(string $text, array $words): bool {
        $text = strtolower($text);
        foreach ($words as $word) {
            if (str_contains($text, strtolower($word))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Replace specific characters in text
     */
    public static function replaceCharacters(string $text, string $search, string $replace): string {
        return str_replace($search, $replace, $text);
    }

    /**
     * Remove multiple spaces and trim
     */
    public static function normalizeSpaces(string $text): string {
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Convert to slug format (lowercase, hyphens)
     */
    public static function toSlug(string $text): string {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        return trim($text, '-');
    }
}