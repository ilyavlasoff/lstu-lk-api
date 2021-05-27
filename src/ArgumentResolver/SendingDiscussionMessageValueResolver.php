<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\SendingDiscussionMessage;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SendingDiscussionMessageValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;
    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return SendingDiscussionMessage::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var SendingDiscussionMessage $message */
        $message = $this->serializer->deserialize($request->getContent(), SendingDiscussionMessage::class, 'json');

        $errors = $this->validator->validate($message);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $message;
    }
}