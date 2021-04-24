<?php

namespace App\Service\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WeekTypeValidationService extends AbstractValidationService
{
    public function __construct(ValidatorInterface $validator)
    {
        parent::__construct($validator);

        $this->typeConstraints = [
            new Assert\Type('string', 'string')
        ];

        $weekTypes = ['white', 'green'];

        $this->valueConstraints = [
            new Assert\Choice($weekTypes,
                null,
                null,
                null,
                null,
                null,
                "Available week types: " . implode(', ', $weekTypes))
        ];
    }
}