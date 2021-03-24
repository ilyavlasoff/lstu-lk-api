<?php

namespace App\Model\Response\Exception;

use JMS\Serializer\Annotation as JMS;

class UserExceptionWarning
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\ReadOnly()
     */
    private $type = "user";

    /**
     * @var int
     * @JMS\Type("int")
     */
    private $code;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $error;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("generic_message")
     */
    private $genericMessage;

    /**
     * @var array
     * @JMS\Type("array")
     */
    private $errorMessages = [];

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
    public function getGenericMessage(): string
    {
        return $this->genericMessage;
    }

    /**
     * @param string $genericMessage
     */
    public function setGenericMessage(string $genericMessage): void
    {
        $this->genericMessage = $genericMessage;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @param array $errorMessages
     */
    public function setErrorMessages(array $errorMessages): void
    {
        $this->errorMessages = $errorMessages;
    }


}