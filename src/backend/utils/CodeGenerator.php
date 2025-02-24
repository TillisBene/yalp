<?php

namespace utils;

class CodeGenerator
{
    /**
     * Generates a random 6-character alphanumeric code
     * @return string
     */
    public static function generate(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
}