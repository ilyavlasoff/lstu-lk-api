<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidUserException extends AbstractSystemException
{
    protected $httpCode = Response::HTTP_UNAUTHORIZED;

    public function __construct(Throwable $previous = null)
    {
        $message = "Logged in user is invalid";

        parent::__construct('ERR_NO_USER', $message, 0, $previous);
    }
}