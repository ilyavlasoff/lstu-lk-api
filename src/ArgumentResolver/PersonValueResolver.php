<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\Person;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    private $personalRepository;

    public function __construct(ValidatorInterface $validator, PersonalRepository $personalRepository)
    {
        $this->validator = $validator;
        $this->personalRepository = $personalRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Person::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $person = new Person();
        $person->setPersonId($request->query->get('p'));

        $errors = $this->validator->validate($person);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $personalExistence = $this->personalRepository->isPersonExists($person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$personalExistence) {
            throw new NotFoundException('Person');
        }

        yield $person;
    }
}