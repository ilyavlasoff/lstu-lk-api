<?php

namespace App\Service\Validation;

use App\Exception\DataAccessException;
use App\Exception\ParameterExistenceException;
use App\Exception\ResourceNotFoundException;
use App\Repository\EducationRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EducationValidationService extends AbstractValidationService
{
    private $educationRepository;

    public function __construct(ValidatorInterface $validator, EducationRepository $educationRepository)
    {
        parent::__construct($validator);
        $this->educationRepository = $educationRepository;

        $this->existenceConstraints = [
            new Assert\NotNull(null, 'Education argument was not founded')
        ];

        $this->typeConstraints = [
            new Assert\Type('string', 'string')
        ];

        $this->valueConstraints = [
            new Assert\NotBlank(null, 'Education argument can not be blank')
        ];
    }

    public function validate($value, $valueParameterName) {
        parent::validate($value, $valueParameterName);

        if(null !== $value) {
            try {
                $educationExistence = $this->educationRepository->isEducationExists($value);
            } catch (\Exception $e) {
                throw new DataAccessException('Education');
            }

            if(!$educationExistence) {
                throw new ResourceNotFoundException('Education', $value);
            }
        }
    }
}