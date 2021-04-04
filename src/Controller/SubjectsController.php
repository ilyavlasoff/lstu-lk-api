<?php

namespace App\Controller;

use App\Repository\PersonalRepository;
use App\Repository\SubjectRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SubjectsController
 * @package App\Controller
 * @Route("/api/v1/student/subjects")
 */
class SubjectsController extends AbstractRestController
{
    private $subjectRepository;

    public function __construct(SerializerInterface $serializer, SubjectRepository $subjectRepository)
    {
        parent::__construct($serializer);
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * @Route("/list/{contingentId}/{semesterId}", name="get_semester_subjects", methods={"GET"})
     * @param string $contingentId
     * @param string $semesterId
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function getSubjectListBySemester(
        string $contingentId,
        string $semesterId,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $groupId = $personalRepository->getGroupByContingent($contingentId);

            if(!$groupId) {
                throw new \Exception('Invalid group');
            }

        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $semesterSubjects = $this->subjectRepository->getSubjectsBySemester($groupId, $semesterId);
        } catch (\Exception $e) {
            throw $e;
        }

        return new JsonResponse(
            $this->serializer->serialize(
                $semesterSubjects,
                'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\AcademicSubject>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }
}