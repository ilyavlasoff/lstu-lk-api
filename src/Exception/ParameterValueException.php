<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ParameterValueException extends AbstractSystemException
{
    protected $httpCode = Response::HTTP_BAD_REQUEST;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $parameterName;

    /**
     * @JMS\Expose()
     */
    public $actualValue;

    /**
     * @var array
     * @JMS\Expose()
     * @JMS\Type(array<string>)
     */
    public $expectedValueProperties;

    public function __construct($parameterName, $actualValue, $expectedValueProperties, Throwable $previous = null)
    {
        $this->parameterName = $parameterName;
        $this->actualValue = $actualValue;
        $this->expectedValueProperties = $expectedValueProperties;

        $message = 'Unexpected argument value';
        parent::__construct('ERR_PARAM_VALUE', $message, 0, $previous);
    }
}