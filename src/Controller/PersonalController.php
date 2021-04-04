<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\InvalidRequestException;
use App\Exception\ValueNotFoundException;
use App\Repository\PersonalRepository;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Model\Mapping\PersonalProperties;

/**
 * Class PersonalController
 * @package App\Controller
 * @Route("/api/v1/person")
 */
class PersonalController extends AbstractRestController
{
    private $personalRepository;

    public function __construct(SerializerInterface $serializer, PersonalRepository $personalRepository)
    {
        parent::__construct($serializer);
        $this->personalRepository = $personalRepository;
    }

    /**
     * @Route("/props/{id}", name="get_person_props", methods={"GET"})
     * @param string $id
     * @return JsonResponse
     * @throws ValueNotFoundException
     */
    public function getPersonProperties(string $id): JsonResponse
    {
        try {
            $personalProps = $this->personalRepository->getPersonalProperties($id);

            if (!$personalProps) {
                throw new ValueNotFoundException('Person', 'Person not found');
            }

            return $this->responseSuccessWithObject($personalProps);

        } catch (\Exception $e)
        {
            throw $e;
        }
    }

}