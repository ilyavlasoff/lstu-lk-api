<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\WorkAnswerAttachment;
use App\Model\QueryParam\Discipline;
use App\Model\QueryParam\Education;
use App\Model\QueryParam\Semester;
use App\Model\QueryParam\StudentWork;
use App\Model\QueryParam\TaskAnswer;
use App\Model\QueryParam\WithJsonFlag;
use App\Model\DTO\ListedResponse;
use App\Repository\DisciplineRepository;
use App\Repository\EducationRepository;
use App\Repository\EducationTaskRepository;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class EducationTasksController
 * @package App\Controller
 * @Route("/api/v1/student/tasks")
 */
class EducationTasksController extends AbstractRestController
{
    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct($serializer);
    }

    /**
     * @Route("/list", name="education_task_list", methods={"GET"})
     *
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param EducationTaskRepository $educationTaskRepository
     * @param EducationRepository $educationRepository
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     *
     * @OA\Get(
     *     tags={"Учебные задания"},
     *     summary="Получение списка учебных заданий",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="dis",
     *          description="Идентификатор дисциплины"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="sem",
     *          description="Идентификатор учебного семестра"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список учебных заданий",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\StudentWork::class, groups={"Default"})))
     *          ))
     *     )
     * )
     */
    public function getEducationTasksList(
        Discipline $discipline,
        Education $education,
        Semester $semester,
        EducationTaskRepository $educationTaskRepository,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException();
        }

        if (!in_array($education->getEducationId(), array_values($currentUserEduList))) {
            throw new AccessDeniedException('Tasks');
        }

        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());
            $workList = $educationTaskRepository->getEducationTasksList($semester->getSemesterId(),
                    $discipline->getDisciplineId(), $group, $education->getEducationId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $workListAnswer = new ListedResponse();
        $workListAnswer->setCount(count($workList));
        $workListAnswer->setPayload($workList);

        return $this->responseSuccessWithObject($workListAnswer);
    }

    /**
     * @Route("", name="education_answer_add", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Учебные задания"},
     *     summary="Добавление нового ответа на учебное задание",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="work",
     *          description="Идентификатор учебной работы"
     *     ),
     *     @OA\RequestBody(
     *          description="Объект нового ответа на учебное задание",
     *          @OA\JsonContent(
     *              ref=@Model(type=WorkAnswerAttachment::class, groups={"Default"})
     *          )
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Успешно добавлено",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  nullable=false,
     *                  type="string",
     *                  description="Идентификатор добавленного ответа"
     *              )
     *          )
     *     )
     * )
     *
     * @param WorkAnswerAttachment $answerAttachment
     * @param Education $education
     * @param StudentWork $studentWork
     * @param WithJsonFlag $withJsonFlag
     * @param EducationTaskRepository $educationTaskRepository
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function addEducationTaskAnswer(
        WorkAnswerAttachment $answerAttachment,
        Education $education,
        StudentWork $studentWork,
        WithJsonFlag $withJsonFlag,
        EducationTaskRepository $educationTaskRepository,
        PersonalRepository $personalRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $isBelongs = $personalRepository->isEducationBelongsToUser($education->getEducationId(), $user->getDbOid());
            if(!$isBelongs) {
                throw new AccessDeniedException('Task');
            }

            $attachments = [];
            if($withJsonFlag->getWithJsonData()) {
                $attachments = array_map(function (Attachment $attachment) {
                    return $attachment->toBinary();
                }, $answerAttachment->getAttachments());

            }

            $createdId = $educationTaskRepository->addEducationTaskAnswer(
                $education->getEducationId(), $studentWork->getWork(), $answerAttachment->getName(),
                $attachments, $answerAttachment->getExtLinks());

        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $createdAttachment = new WorkAnswerAttachment();
        $createdAttachment->setId($createdId);

        return $this->responseCreated($createdAttachment);
    }

    /**
     * @Route("/doc", name="education_answer_doc_add", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Учебные задания"},
     *     summary="Добавление нового файла к ответу на задание",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="answer",
     *          description="Идентификатор ответа на учебное задание"
     *     ),
     *     @OA\RequestBody(
     *          description="Медиа-файл, добавляемый к сообщению",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Property(
     *                  property="attachment",
     *                  type="file",
     *                  description="Файл, добавляемый ответу на учебное задание"
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *          response="200",
     *          description="Медиа-файл успешно добавлен"
     *      )
     * )
     *
     * @param BinaryFile $binaryFile
     * @param TaskAnswer $answer
     * @param EducationTaskRepository $educationTaskRepository
     * @return JsonResponse
     */
    public function addEducationTaskAnswerDocument(
        BinaryFile $binaryFile,
        TaskAnswer $answer,
        EducationTaskRepository $educationTaskRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $isUserValid = $educationTaskRepository->getUserIsSenderForAttachment($user->getDbOid(), $answer->getAnswer());

            if(!$isUserValid) {
                throw new AccessDeniedException('Answer');
            }

            $educationTaskRepository->addAnswerDocument($binaryFile, $answer->getAnswer());

        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccess();
    }

}