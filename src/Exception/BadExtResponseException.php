<?php

namespace App\Exception;

class BadExtResponseException extends RestException
{
    public $data;

    public function __construct(int $statusCode, string $data)
    {
        $this->data = $data;
        parent::__construct('EXT_BAD_RESPONSE', 'External server returns error', $statusCode, [], null, [], 0);
    }
}