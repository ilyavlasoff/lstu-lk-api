<?php

namespace App\Service\Validation;

use App\Exception\DataAccessException;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\ResourceNotFoundException;
use App\Repository\DisciplineRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisciplineValidationService extends AbstractValidationService
{
    private $disciplineRepository;

    public function __construct(ValidatorInterface $validator,DisciplineRepository $disciplineRepository)
    {
        parent::__construct($validator);
        $this->disciplineRepository = $disciplineRepository;

        $this->existenceConstraints = [
            new Assert\NotNull(null, 'Discipline argument was not founded')
        ];

        $this->typeConstraints = [
            new Assert\Type('string', 'string')
        ];

        $this->valueConstraints = [
            new Assert\NotBlank(null, 'Discipline argument can not be blank')
        ];
    }

    public function validate($value, $valueParameterName) {
        parent::validate($value, $valueParameterName);

        if(null !== $value) {
            try {
                $disciplineExistence = $this->disciplineRepository->isDisciplineExists($value);
            } catch (\Exception $e) {
                throw new DataAccessException('Discipline');
            }

            if(!$disciplineExistence) {
                throw new ResourceNotFoundException('Discipline', $value);
            }
        }
    }
}