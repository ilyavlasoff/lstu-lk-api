<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends AbstractRestException
{
    public function __construct(string $objectName = "")
    {
        $error = 'ERR_NOT_FOUND';
        $message = $objectName ? "Requested resource $objectName was not found" : "Resource was not found";

        parent::__construct($error, $message, Response::HTTP_NOT_FOUND);
    }
}