<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use JMS\Serializer\Annotation as JMS;

class DataAccessException extends AbstractSystemException
{
    /**
     * @var string
     * Resource name
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $resource;

    protected $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct($resource, Throwable $previous = null)
    {
        $this->resource = $resource;
        $message = 'Unable to load from resource';
        parent::__construct('ERR_DATA_ACCESS', $message, 0, $previous);
    }
}