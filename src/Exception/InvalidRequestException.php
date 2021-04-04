<?php

namespace App\Exception;

use App\Model\Response\Exception\UserExceptionWarning;
use Symfony\Component\HttpFoundation\Response;

class InvalidRequestException extends \Exception implements IUserException
{
    private const errcode = 'INV_REQUEST';

    public function toUserExceptionWarning(): UserExceptionWarning
    {
        $warning = new UserExceptionWarning();
        $warning->setError(self::errcode);
        $warning->setGenericMessage($this->message);
        $warning->setCode(Response::HTTP_BAD_REQUEST);
        return $warning;
    }
}