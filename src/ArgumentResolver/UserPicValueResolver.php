<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ResourceNotFoundException;
use App\Exception\ValidationException;
use App\Model\Request\UserPic;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserPicValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;

    private $validator;

    private $personalRepository;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, PersonalRepository $personalRepository)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->personalRepository = $personalRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return UserPic::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var UserPic $userPicRequest */
        $userPicRequest = $this->serializer->deserialize($request->getContent(), UserPic::class, 'json');

        $errors = $this->validator->validate($userPicRequest);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $personalExistence = $this->personalRepository->isPersonExists($userPicRequest->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$personalExistence) {
            throw new NotFoundException('Person');
        }

        yield $userPicRequest;
    }
}