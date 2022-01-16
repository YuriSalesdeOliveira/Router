<?php

namespace YuriOliveira\Router;

class Redirect
{
    public static function redirect(string $to)
    {
        header("Location: {$to}");

        exit();
    }
}