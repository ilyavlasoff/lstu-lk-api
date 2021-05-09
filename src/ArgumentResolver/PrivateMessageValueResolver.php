<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\PrivateMessage;
use App\Repository\PrivateMessageRepository;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PrivateMessageValueResolver implements ArgumentValueResolverInterface
{
    private $validator;
    private $privateMessageRepository;

    public function __construct(ValidatorInterface $validator, PrivateMessageRepository $privateMessageRepository)
    {
        $this->validator = $validator;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return PrivateMessage::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $messageId = $request->query->get('pmsg');

        $privateMessage = new PrivateMessage();
        $privateMessage->setMsg($messageId);

        $errors = $this->validator->validate($privateMessage);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $isExists = $this->privateMessageRepository->getMessageExists($privateMessage->getMsg());
        } catch (Exception | \Doctrine\DBAL\Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$isExists) {
            throw new NotFoundException('Message');
        }

        yield $privateMessage;
    }
}