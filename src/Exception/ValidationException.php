<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends RestException
{
    public function __construct(ConstraintViolationListInterface $violationList, string $objectName = "")
    {
        $errors = [];
        foreach ($violationList as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        $error = 'ERR_VALIDATION';
        $message = $objectName ? "Object $objectName is invalid" : "Request data is invalid";

        parent::__construct($error, $message, Response::HTTP_BAD_REQUEST, $errors);
    }
}