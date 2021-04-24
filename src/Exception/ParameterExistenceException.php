<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use JMS\Serializer\Annotation as JMS;

class ParameterExistenceException extends AbstractSystemException
{
    protected $httpCode = Response::HTTP_BAD_REQUEST;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $parameterName;

    public function __construct($message, $parameterName, Throwable $previous = null)
    {
        $this->parameterName = $parameterName;
        parent::__construct('ERR_PARAM_EXISTENCE', $message, 0, $previous);
    }

}