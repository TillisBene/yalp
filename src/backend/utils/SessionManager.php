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
}

