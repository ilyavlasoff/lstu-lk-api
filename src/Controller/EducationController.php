<?php

namespace App\Controller;

use App\Repository\EducationRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\Mapping\Education;

/**
 * Class EducationController
 * @package App\Controller
 * @Route("/api/v1/student/edu")
 */
class EducationController extends AbstractRestController
{
    private $educationRepository;

    public function __construct(SerializerInterface $serializer, EducationRepository $educationRepository)
    {
        parent::__construct($serializer);
        $this->educationRepository = $educationRepository;
    }

    /**
     * @Route("/list/{personId}", name="get_educations_list", methods={"GET"})
     *
     * @param string $personId
     * @return JsonResponse
     */
    public function getEducationList(string $personId): JsonResponse
    {
        $educationList = $this->educationRepository->getLstuEducationListByPerson($personId);

        return new JsonResponse(
            $this->serializer->serialize($educationList, 'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\Education>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/{educationId}", name="get_education_details", methods={"GET"})
     */
    public function getEducationDetails(): JsonResponse
    {
        return new JsonResponse();
    }
}