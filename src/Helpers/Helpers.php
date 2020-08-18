<?php

namespace App\Helpers;

class Helpers
{
    static function sanitize($var)
    {
        return htmlspecialchars(strip_tags($var));
    }

}