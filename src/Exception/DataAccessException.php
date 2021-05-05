<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class DataAccessException extends RestException
{
    public function __construct(\Throwable $previous = null)
    {
        $error = 'ERR_DATA_ACCESS';
        $message = "Failed to access data";
        parent::__construct($error, $message, Response::HTTP_INTERNAL_SERVER_ERROR, [], $previous);
    }
}