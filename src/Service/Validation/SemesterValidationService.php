<?php

namespace App\Service\Validation;

use App\Exception\DataAccessException;
use App\Exception\ResourceNotFoundException;
use App\Repository\EducationRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SemesterValidationService extends AbstractValidationService
{
    private $educationRepository;

    public function __construct(ValidatorInterface $validator, EducationRepository $educationRepository)
    {
        parent::__construct($validator);
        $this->educationRepository = $educationRepository;

        $this->existenceConstraints = [
            new Assert\NotNull(null, 'Semester parameter was not founded')
        ];

        $this->typeConstraints = [
            new Assert\Type('string', 'string')
        ];

        $this->valueConstraints = [
            new Assert\NotBlank(null, 'Semester argument can not ba blank')
        ];
    }

    public function validate($value, $valueParameterName) {
        parent::validate($value, $valueParameterName);

        if(null !== $value) {
            try {
                $semesterExistence = $this->educationRepository->isSemesterExists($value);
            } catch (\Exception $e) {
                throw new DataAccessException('Semester');
            }

            if (!$semesterExistence) {
                throw new ResourceNotFoundException('Semester', $value);
            }
        }
    }
}