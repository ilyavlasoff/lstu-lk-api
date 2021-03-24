<?php

namespace App\Exception;

use App\Model\Response\Exception\UserExceptionWarning;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class ValidationException extends \Exception implements IUserException
{
    private const errcode = 'ERR_VALIDATION';
    private $validationErrors;

    public function __construct(ConstraintViolationListInterface $validationErrors, string $className, Throwable $previous = null, $code = 0)
    {
        $this->validationErrors = [];
        foreach ($validationErrors as $violation) {
            $this->validationErrors[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        parent::__construct("Validation error occurred in $className", $code, $previous);
    }

    public function toUserExceptionWarning(): UserExceptionWarning
    {
        $warning = new UserExceptionWarning();
        $warning->setCode(Response::HTTP_BAD_REQUEST);
        $warning->setError(self::errcode);
        $warning->setGenericMessage($this->getMessage());
        $warning->setErrorMessages($this->validationErrors);
        return $warning;
    }
}
