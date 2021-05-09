<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\DisciplineDiscussionMessage;
use App\Repository\DisciplineDiscussionRepository;
use App\Repository\DisciplineRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DisciplineDiscussionMessageValueResolver implements ArgumentValueResolverInterface
{
    private $validator;
    private $disciplineDiscussionRepository;

    public function __construct(ValidatorInterface $validator, DisciplineDiscussionRepository $disciplineDiscussionRepository)
    {
        $this->validator = $validator;
        $this->disciplineDiscussionRepository = $disciplineDiscussionRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return DisciplineDiscussionMessage::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $messageId = $request->query->get('msg');

        $message = new DisciplineDiscussionMessage();
        $message->setMsg($messageId);

        $errors = $this->validator->validate($message);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $isExists = $this->disciplineDiscussionRepository->isMessageExists($message->getMsg());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$isExists) {
            throw new NotFoundException('DisciplineDiscussionMessage');
        }

        yield $message;
    }
}