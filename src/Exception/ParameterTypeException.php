<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Annotation as JMS;
use Throwable;

class ParameterTypeException extends AbstractSystemException
{
    protected $httpCode = Response::HTTP_BAD_REQUEST;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Expose()
     */
    public $parameterName;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Expose()
     */
    public $actualType;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Expose()
     */
    public $expectedType;

    public function __construct($parameterName, $actualType, $expectedType, $code = 0, Throwable $previous = null)
    {
        $this->parameterName = $parameterName;
        $this->actualType = $actualType;
        $this->expectedType = $expectedType;
        $message = 'Given parameter type is unexpected';

        parent::__construct('ERR_PARAM_TYPE', $message, $code, $previous);
    }
}