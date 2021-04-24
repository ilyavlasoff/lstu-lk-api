<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class ValidationException extends AbstractUserException
{
    protected $httpCode = Response::HTTP_BAD_REQUEST;

    /**
     * @var array
     * @JMS\Type("array<string>")
     * @JMS\Expose()
     * Array of validation errors
     */
    public $validationErrors;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     * Resource name
     */
    public $resource;

    public function __construct(
        ConstraintViolationListInterface $validationErrors,
        string $resource,
        Throwable $previous = null,
        $code = 0
    ) {
        $this->resource = $resource;
        $this->validationErrors = [];
        foreach ($validationErrors as $violation) {
            $this->validationErrors[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        parent::__construct(
            'ERR_VALIDATION',
            "Validation errors occurred",
            "Обнаружен ввод некорректных значений",
            $code,
            $previous
        );
    }
}
