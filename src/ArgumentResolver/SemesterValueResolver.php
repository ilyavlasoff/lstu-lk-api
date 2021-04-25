<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\Request\Semester;
use App\Repository\EducationRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SemesterValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;

    private $validator;

    private $educationRepository;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, EducationRepository $educationRepository)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->educationRepository = $educationRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Semester::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var  Semester $semester */
        $semester = $this->serializer->deserialize($request->getContent(), Semester::class, 'json');

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