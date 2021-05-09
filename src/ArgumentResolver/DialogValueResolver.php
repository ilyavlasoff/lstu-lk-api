<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\Dialog;
use App\Repository\PrivateMessageRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DialogValueResolver implements ArgumentValueResolverInterface
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
        return Dialog::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $dialog = new Dialog();
        $dialog->setDialogId($request->query->get('dialog'));

        $errors = $this->validator->validate($dialog);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $isExists = $this->privateMessageRepository->getDialogExists($dialog->getDialogId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        if(!$isExists) {
            throw new NotFoundException('Dialog');
        }

        yield $dialog;
    }
}