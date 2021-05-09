<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\DTO\BinaryFile;
use App\Model\QueryParam\DisciplineDiscussionMessage;
use App\Model\QueryParam\PrivateMessage;
use App\Model\QueryParam\TaskAnswer;
use App\Model\QueryParam\TeachingMaterial;
use App\Repository\DisciplineDiscussionRepository;
use App\Repository\DisciplineRepository;
use App\Repository\EducationTaskRepository;
use App\Repository\PrivateMessageRepository;
use App\Repository\TeachingMaterialsRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MultipartController
 * @package App\Controller
 */
class MultipartController extends AbstractController
{
    private function createFileResponse($fileContent, $fileName)
    {
        $fInfo = finfo_open(FILEINFO_MIME);
        $mimeType = finfo_buffer($fInfo, $fileContent);

        return new Response($fileContent, Response::HTTP_OK, [
            'Content-Type' => $mimeType ?? 'application/octet-stream',
            'Content-Length' => strlen($fileContent),
            'Content-Disposition', 'attachment; filename="' . $fileName . '";'
        ]);
    }

    /**
     * @Route("/api/v1/materials/doc", name="discipline_materials_attachment_get", methods={"GET"})
     *
     * @param TeachingMaterial $material
     * @param TeachingMaterialsRepository $teachingMaterialsRepository
     * @return Response
     */
    public function getDisciplineMaterialsDocument(
        TeachingMaterial $material,
        TeachingMaterialsRepository $teachingMaterialsRepository
    ): Response
    {
        try {
            $attachment = $teachingMaterialsRepository->getTeachingMaterialsAttachment($material->getMaterial());
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->createFileResponse($attachment->getFileContent(), $attachment->getFilename());
    }

    /**
     * @Route("/api/v1/student/tasks/doc", name="education_answer_attachment_get", methods={"GET"})
     *
     * @param TaskAnswer $answer
     * @param EducationTaskRepository $educationTaskRepository
     * @return Response
     */
    public function getEducationTaskAnswerDocument(
        TaskAnswer $answer,
        EducationTaskRepository $educationTaskRepository
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $isUserValid = $educationTaskRepository->getUserIsSenderForAttachment($user->getDbOid(), $answer->getAnswer());

            if(!$isUserValid) {
                throw new AccessDeniedException('Answer');
            }

            /** @var BinaryFile $file */
            $file = $educationTaskRepository->getEducationTaskAnswer($answer->getAnswer());

        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->createFileResponse($file->getFileContent(), $file->getFilename());
    }

    /**
     * @Route("/api/v1/discussion/doc", name="discipline_discussion_message_attachment_get", methods={"GET"})
     *
     * @param DisciplineDiscussionMessage $message
     * @param DisciplineDiscussionRepository $disciplineDiscussionRepository
     * @return Response
     */
    public function getDisciplineDiscussionDocument(
        DisciplineDiscussionMessage $message,
        DisciplineDiscussionRepository $disciplineDiscussionRepository
    ): Response
    {
        try {
            $file = $disciplineDiscussionRepository->getMessageAttachment($message->getMsg());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->createFileResponse($file->getFileContent(), $file->getFilename());
    }

    /**
     * @Route("/api/v1/messenger/doc", name="private_message_attachment_get", methods={"GET"})
     *
     * @param PrivateMessage $privateMessage
     * @param PrivateMessageRepository $privateMessageRepository
     * @return JsonResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getPrivateMessageDocument(
        PrivateMessage $privateMessage,
        PrivateMessageRepository $privateMessageRepository
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        try {
            $dialog = $privateMessageRepository->getDialogByMessage($privateMessage->getMsg());
            $participants = $privateMessageRepository->getDialogParticipants($dialog);

            if(!in_array($user->getDbOid(), $participants)) {
                throw new AccessDeniedException('Message');
            }

            $file = $privateMessageRepository->getPrivateMessageAttachment($privateMessage->getMsg());

        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->createFileResponse($file->getFileContent(), $file->getFilename());
    }
}