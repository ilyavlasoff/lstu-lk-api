<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\Annotation as JMS;

abstract class AbstractRestException extends HttpException
{
    /**
     * @JMS\Type("string")
     */
    private $error;

    /**
     * @JMS\Type("array")
     */
    private $details;

    public function __construct(string $error, string $message, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, array $details = [], \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        $this->error = $error;
        $this->details = $details;
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("code")
     */
    public function getErrorCode()
    {
        return $this->getStatusCode();
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("message")
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