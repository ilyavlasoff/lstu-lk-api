<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends AbstractRestException
{
    public function __construct(string $resourceName = "")
    {
        $error = 'ERR_DENIED';
        $message = $resourceName ? "Unable to access required $resourceName" : "Access denied";

        parent::__construct($error, $message, Response::HTTP_FORBIDDEN);
    }
}