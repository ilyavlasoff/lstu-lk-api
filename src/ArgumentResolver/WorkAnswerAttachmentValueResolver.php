<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\DTO\WorkAnswerAttachment;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WorkAnswerAttachmentValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;
    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return WorkAnswerAttachment::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var WorkAnswerAttachment $workAttachment */
        $workAttachment = $this->serializer->deserialize($request->getContent(), WorkAnswerAttachment::class, 'json');

        $errors = $this->validator->validate($workAttachment);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $workAttachment;
    }
}