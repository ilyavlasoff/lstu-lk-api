<?php

namespace App\Exception;

use App\Model\Response\Exception\SystemExceptionWarning;

interface ISystemException
{
    public function toSystemExceptionWarning(): SystemExceptionWarning;
}