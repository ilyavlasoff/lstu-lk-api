<?php

namespace App\Exception;

use App\Model\Response\Exception\SystemExceptionWarning;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InheritedSystemException extends \Exception implements ISystemException
{
    private const userErrorCode = "ERR_SYSTEM";
    private $userMessage;

    public function __construct(Throwable $previous = null, $systemMessage = "", $code = 0, $userMessage = "A system error occurred")
    {
        $this->userMessage = $userMessage;
        parent::__construct($systemMessage, $code, $previous);
    }

    public function toSystemExceptionWarning(): SystemExceptionWarning
    {
        $warning = new SystemExceptionWarning();
        $warning->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $warning->setError(self::userErrorCode);
        $warning->setMessage($this->userMessage);
        return $warning;
    }
}