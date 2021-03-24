<?php

namespace App\Model\Response\Exception;

use JMS\Serializer\Annotation as JMS;

class SystemExceptionWarning
{
    /**
     * @JMS\Type("string")
     * @JMS\ReadOnly()
     * @var string
     */
    private $type = "system";

    /**
     * @JMS\Type("int")
     * @var int
     */
    private $code;

    /**
     * @JMS\Type("string")
     * @var string
     */
    private $error;

    /**
     * @JMS\Type("string")
     * @var string
     */
    private $message;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }



}