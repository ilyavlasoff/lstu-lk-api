<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\Annotation as JMS;

class RestException extends HttpException
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups("rest-response")
     */
    private $error;

    /**
     * @JMS\Type("array")
     * @JMS\Groups("rest-response")
     */
    private $details;

    public function __construct(
        string $error,
        string $message,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $details = [],
        \Throwable $previous = null,
        array $headers = [],
        ?int $code = 0
    ){
        $this->error = $error;
        $this->details = $details;
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("code")
     * @JMS\Groups("rest-response")
     */
    public function getErrorCode()
    {
        return $this->getStatusCode();
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("message")
     * @JMS\Groups("rest-response")
     */
    public function getErrorMessage()
    {
        return $this->getMessage();
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

}