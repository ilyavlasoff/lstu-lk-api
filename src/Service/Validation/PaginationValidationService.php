<?php

namespace App\Service\Validation;

use App\Exception\ParameterExistenceException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaginationValidationService extends AbstractValidationService
{
    public function __construct(ValidatorInterface $validator)
    {
        parent::__construct($validator);

        $this->typeConstraints = [
            new Assert\Type('int', 'integer')
        ];

        $this->valueConstraints = [
            new Assert\GreaterThanOrEqual(0, null, 'Value must be above zero')
        ];
    }

}