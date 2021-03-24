<?php

namespace App\Exception;

use App\Model\Response\Exception\UserExceptionWarning;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DuplicateValueException extends \Exception implements IUserException
{
    private const errcode = 'ERR_DUPLICATE';
    private $duplicatedValues;

    public function __construct($duplicateValues, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->duplicatedValues = $duplicateValues;
        parent::__construct($message, $code, $previous);
    }

    public function toUserExceptionWarning(): UserExceptionWarning
    {
        $warning = new UserExceptionWarning();
        $warning->setCode(Response::HTTP_BAD_REQUEST);
        $warning->setError(self::errcode);
        $warning->setErrorMessages($this->duplicatedValues);
        $warning->setGenericMessage($this->message);
        return $warning;
    }
}