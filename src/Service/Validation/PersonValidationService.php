<?php

namespace App\Service\Validation;

use App\Exception\DataAccessException;
use App\Exception\ParameterExistenceException;
use App\Exception\ResourceNotFoundException;
use App\Repository\PersonalRepository;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonValidationService extends AbstractValidationService
{
    private $personalRepository;

    public function __construct(ValidatorInterface $validator, PersonalRepository $personalRepository)
    {
        parent::__construct($validator);
        $this->personalRepository = $personalRepository;

        $this->existenceConstraints = [
            new NotNull(null, 'Person argument was not founded')
        ];

        $this->typeConstraints = [
            new Assert\Type('string', 'string')
        ];

        $this->valueConstraints = [
            new NotNull(null, 'Person argument can not be blank')
        ];
    }

    public function validate($value, $valueParameterName) {
        parent::validate($value, $valueParameterName);

        if(null !== $value) {
            try {
                $personalExistence = $this->personalRepository->isPersonExists($value);
            } catch (\Exception $e) {
                throw new DataAccessException('Person');
            }

            if(!$personalExistence) {
                throw new ResourceNotFoundException('Person', $value);
            }
        }
    }
}