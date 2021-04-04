<?php

namespace App\Controller;

use App\Repository\PersonalRepository;
use App\Repository\SubjectRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use App\Model\Mapping\AcademicSubject;

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
     * @Route("/list", name="get_semester_subjects", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Дисциплины"},
     *     summary="Список дисциплин студента в заданном семестре",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          name="edu",
     *          in="query",
     *          description="Идентификатор периода обучения в ЛГТУ",
     *          required=true
     *     ),
     *     @OA\Parameter(
     *          name="sem",
     *          in="query",
     *          description="Идентификатор семестра, для которого выводится список дисциплин",
     *          required=true
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Массив объектов семестра",
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=AcademicSubject::class, groups={"Default"}))))
     *     )
     * )
     *
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSubjectListBySemester(Request $request, PersonalRepository $personalRepository): JsonResponse
    {
        $educationId = $request->query->get('edu');
        $semesterId = $request->query->get('sem');

        try {
            $groupId = $personalRepository->getGroupByContingent($educationId);

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