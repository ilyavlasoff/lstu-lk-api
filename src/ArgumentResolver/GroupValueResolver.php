<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\Group;
use App\Repository\EducationRepository;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EducationRepository
     */
    private $educationRepository;

    public function __construct(ValidatorInterface $validator, EducationRepository $educationRepository)
    {
        $this->educationRepository = $educationRepository;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->getType() === Group::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $group = new Group();
        $group->setGroupId($request->query->get('g'));

        $errors = $this->validator->validate($group);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        try {
            $groupExistence = $this->educationRepository->isGroupExists($group->getGroupId());
        } catch (Exception | \Doctrine\DBAL\Exception $e) {
            throw new DataAccessException();
        }

        if(!$groupExistence) {
            throw new NotFoundException('Group');
        }

        yield $group;
    }
}