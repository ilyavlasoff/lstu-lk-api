<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ResourceNotFoundException;
use App\Exception\ValidationException;
use App\Model\Request\Discipline;
use App\Repository\DisciplineRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisciplineValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;

    private $validator;

    private $disciplineRepository;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, DisciplineRepository $disciplineRepository)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->disciplineRepository = $disciplineRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Discipline::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var Discipline $discipline */
        $discipline = $this->serializer->deserialize($request->getContent(), Discipline::class, 'json');

        $errors = $this->validator->validate($discipline);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $disciplineExistence = $this->disciplineRepository->isDisciplineExists($discipline->getDisciplineId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$disciplineExistence) {
            throw new NotFoundException('Discipline');
        }

        yield $discipline;
    }
}