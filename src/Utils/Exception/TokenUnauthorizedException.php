<?php

namespace App\Utils\Exception;

use Exception;
use Override;

class TokenUnauthorizedException extends Exception
{
    #[Override]
    protected $code = 401;
}