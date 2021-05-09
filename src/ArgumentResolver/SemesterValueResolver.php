<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\Semester;
use App\Repository\EducationRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SemesterValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    private $educationRepository;

    public function __construct(ValidatorInterface $validator, EducationRepository $educationRepository)
    {
        $this->validator = $validator;
        $this->educationRepository = $educationRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Semester::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $semester = new Semester();
        $semester->setSemesterId($request->query->get('sem'));

        $errors = $this->validator->validate($semester);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $semesterExistence = $this->educationRepository->isSemesterExists($semester->getSemesterId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        if (!$semesterExistence) {
            throw new NotFoundException('Semester');
        }

        yield $semester;
    }
}