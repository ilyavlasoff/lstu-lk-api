<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\Request\Discipline;
use App\Model\Request\Education;
use App\Repository\EducationRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EducationValueResolver implements ArgumentValueResolverInterface
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
        return Education::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var Education $education */
        $education = $this->serializer->deserialize($request->getContent(), Education::class, 'json');

        $errors = $this->validator->validate($education);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $educationExistence = $this->educationRepository->isEducationExists($education->getEducationId());
        } catch (Exception $e) {
            throw new DataAccessException();
        }

        if(!$educationExistence) {
            throw new NotFoundException('Education');
        }

        yield $education;
    }
}
