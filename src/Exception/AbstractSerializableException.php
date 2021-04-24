<?php

namespace App\Exception;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

/**
 * Class AbstractSerializableException
 * @package App\Exception
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractSerializableException extends \Exception
{
    protected $httpCode;

    /**
     * @var string
     * @JMS\Expose()
     */
    public $error;

    /**
     * @var string
     * @JMS\Expose()
     */
    public $message;

    /**
     * @var \DateTime
     * @JMS\Expose()
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uT'>")
     */
    public $time;

    public function __construct($error, $message, $code = 0, Throwable $previous = null)
    {
        $this->error = $error;
        $this->time = new \DateTime();
        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}