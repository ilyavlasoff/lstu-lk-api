<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\TaskAnswer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskAnswerValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return TaskAnswer::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $taskAnswerId = $request->query->get('answer');
        $taskAnswer = new TaskAnswer();
        $taskAnswer->setAnswer($taskAnswerId);

        $errors = $this->validator->validate($taskAnswer);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $taskAnswer;
    }
}