<?php

namespace App\Exception;

use JMS\Serializer\Annotation as JMS;
use Throwable;

abstract class AbstractUserException extends AbstractSerializableException
{
    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $type = 'user';

    /**
     * @var string
     * @JMS\Type("string")
     */
    public $userMessage;

    public function __construct($error, $message, $userMessage, $code = 0, Throwable $previous = null)
    {
        $this->userMessage = $userMessage;
        parent::__construct($error, $message, $code, $previous);
    }
}