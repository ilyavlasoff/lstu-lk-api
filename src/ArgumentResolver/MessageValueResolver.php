<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\Request\Message;
use App\Repository\DisciplineRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageValueResolver implements ArgumentValueResolverInterface
{
    private $validator;
    private $disciplineRepository;

    public function __construct(ValidatorInterface $validator, DisciplineRepository $disciplineRepository)
    {
        $this->validator = $validator;
        $this->disciplineRepository = $disciplineRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Message::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $messageId = $request->query->get('msg');

        $message = new Message();
        $message->setMsg($messageId);

        $errors = $this->validator->validate($message);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $isExists = $this->disciplineRepository->isMessageExists($message->getMsg());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$isExists) {
            throw new NotFoundException('Message');
        }

        yield $message;
    }
}