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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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