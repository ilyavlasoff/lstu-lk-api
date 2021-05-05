<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class DuplicateValueException extends RestException
{
    public function __construct(string $resourceName = "")
    {
        $error = 'ERR_DUPLICATE';
        $message = $resourceName ? "This $resourceName is already exists" : 'Resource is already exists';

        parent::__construct($error, $message, Response::HTTP_BAD_REQUEST);
    }
}