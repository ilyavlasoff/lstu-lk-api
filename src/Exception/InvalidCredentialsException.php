<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidCredentialsException extends AbstractUserException
{
    protected $httpCode = Response::HTTP_UNAUTHORIZED;

    public function __construct(Throwable $previous = null)
    {
        $message = 'Incorrect credentials';
        $userMessage = 'Не удалось найти пользователя с заданными учетными данными.';
        parent::__construct('ERR_INVALID_CREDENTIALS', $message, $userMessage, 0, $previous);
    }
}