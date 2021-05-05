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
    private $validator;

    private $educationRepository;

    public function __construct(ValidatorInterface $validator, EducationRepository $educationRepository)
    {
        $this->validator = $validator;
        $this->educationRepository = $educationRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Education::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $education = new Education();
        $education->setEducationId($request->query->get('edu'));

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
