<?php

namespace App\Utils\Exception;

use Exception;

class TokenUnauthorizedException extends Exception
{
    protected $code = 401;
}