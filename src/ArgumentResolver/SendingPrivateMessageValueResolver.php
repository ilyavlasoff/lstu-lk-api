<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\SendingPrivateMessage;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SendingPrivateMessageValueResolver implements ArgumentValueResolverInterface
{
    private $validator;
    private $serializer;

    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return SendingPrivateMessage::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var SendingPrivateMessage $privateMessage */
        $privateMessage = $this->serializer->deserialize(
            $request->getContent(), SendingPrivateMessage::class, 'json');

        $errors = $this->validator->validate($privateMessage);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $privateMessage;
    }
}