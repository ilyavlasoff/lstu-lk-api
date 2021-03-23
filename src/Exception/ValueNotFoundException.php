<?php

namespace App\Exception;

use App\Model\Response\Exception\UserExceptionWarning;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValueNotFoundException extends \Exception implements IUserException
{
    private const code = "ERR_NOTFOUND";
    private $notFoundValue;

    public function __construct($notFoundValue, $message = "Value not found", $code = 0, Throwable $previous = null)
    {
        $this->notFoundValue = $notFoundValue;
        parent::__construct($message, $code, $previous);
    }

    public function toUserExceptionWarning(): UserExceptionWarning
    {
        $warning = new UserExceptionWarning();
        $warning->setCode(Response::HTTP_NOT_FOUND);
        $warning->setError(self::code);
        $warning->setErrorMessages([$this->notFoundValue => $this->message]);
        $warning->setGenericMessage($this->message);
        return $warning;
    }
}