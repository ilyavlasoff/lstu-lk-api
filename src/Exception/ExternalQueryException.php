<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ExternalQueryException extends RestException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(
            'ETT_EXT_SERVICE',
            'External service is unavailable',
            Response::HTTP_INTERNAL_SERVER_ERROR, [], $previous, [], 0);
    }
}