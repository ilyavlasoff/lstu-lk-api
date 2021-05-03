<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class SystemException extends AbstractRestException
{
    public function __construct(\Throwable $previous = null)
    {
        $error = 'ERR_SYSTEM';
        $message = 'Unexpected error has occurred';

        parent::__construct($error, $message, Response::HTTP_INTERNAL_SERVER_ERROR, [], $previous);
    }
}