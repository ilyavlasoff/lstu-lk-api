<?php

namespace App\Exception;

use App\Model\Response\Exception\UserExceptionWarning;

interface IUserException
{
    public function toUserExceptionWarning(): UserExceptionWarning;
}