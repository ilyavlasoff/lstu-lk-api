<?php

namespace App\Controller;

use App\Document\User;
use App\Model\ExternalConsumingParam\NotificationReceiver;
use App\Model\ExternalConsumingParam\NotificationReceiverDevice;
use App\Model\QueryParam\NotificationPreferences;
use App\Model\QueryParam\NotifiedDevice;
use App\Service\NotifierQueryService;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class NotificationController
 * @package App\Controller
 * @Route("/api/v1/notifications")
 */
class NotificationController extends AbstractRestController
{
    private $notifierQueryService;

    public function __construct(SerializerInterface $serializer, NotifierQueryService $notifierQueryService)
    {
        parent::__construct($serializer);
        $this->notifierQueryService = $notifierQueryService;
    }

    /**
     * @Route("/device", name="add_notified_device", methods={"POST"})
     * @param NotifiedDevice $device
     * @return JsonResponse
     *
     * @OA\Post(
     *     tags={"Уведомления"},
     *     summary="Добавление устройства",
     *     @Security(name="Bearer"),
     *     @OA\RequestBody(
     *          description="Добавляемое устройство для FCM уведомлений",
     *          @OA\JsonContent(
     *              ref=@Model(type="NotifiedDevice::class")
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Устройство добавлено",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  description="Флаг успешного добавления",
     *                  example="true"
     *              )
     *          )
     *     )
     * )
     */
    public function addNotifiedDevice(NotifiedDevice $device)
    {
        /** @var User $user */
        $user = $this->getUser();

        $notificationReceiverDevice = new NotificationReceiverDevice();
        $notificationReceiverDevice->setUser($user->getDbOid());
        $notificationReceiverDevice->setFcmKey($device->getFcmKey());

        $added = $this->notifierQueryService->addUserDevice($notificationReceiverDevice);

        return $this->responseSuccess();
    }

    /**
     * @Route("/device", name="remove_notified_device", methods={"DELETE"})
     * @param NotifiedDevice $device
     * @return JsonResponse
     *
     * @OA\Delete(
     *     tags={"Уведомления"},
     *     summary="Удаление устройства",
     *     @Security(name="Bearer"),
     *     @OA\RequestBody(
     *          description="Добавляемое устройство для FCM уведомлений",
     *          @OA\JsonContent(
     *              ref=@Model(type="NotifiedDevice::class")
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешно удалено",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  description="Флаг успешного удаления",
     *                  example="true"
     *              )
     *          )
     *     )
     * )
     */
    public function removeNotifiedDevice(NotifiedDevice $device)
    {
        /** @var User $user */
        $user = $this->getUser();

        $notificationReceiverDevice = new NotificationReceiverDevice();
        $notificationReceiverDevice->setUser($user->getDbOid());
        $notificationReceiverDevice->setFcmKey($device->getFcmKey());

        $added = $this->notifierQueryService->removeUserDevice($notificationReceiverDevice);

        return $this->responseSuccess();
    }

    /**
     * @Route("/prefs", name="get_notification_prefs", methods={"GET"})
     * @return JsonResponse
     *
     * @OA\Get(
     *     tags={"Уведомления"},
     *     summary="Получение настроек уведомлений пользователя",
     *     @Security(name="Bearer"),
     *     @OA\Response(
     *          response="200",
     *          description="Объект настроек уведомлений",
     *          @OA\JsonContent(
     *              ref=@Model(type=NotificationPreferences::class, groups={"Default"})
     *          )
     *     )
     * )
     */
    public function getCurrentNotificationPreferences()
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var NotificationPreferences $preferences */
        $preferences = $this->notifierQueryService->getReceiver($user->getDbOid());

        return $this->responseSuccessWithObject($preferences);
    }

    /**
     * @Route("/prefs", name="patch_notification_prefs", methods={"PATCH"})
     * @param NotificationPreferences $notificationPreferences
     * @return JsonResponse
     *
     * @OA\Patch(
     *     tags={"Уведомления"},
     *     summary="Изменение настроек уведомлений пользователя",
     *     @Security(name="Bearer"),
     *     @OA\RequestBody(
     *          description="Объект изменения настроек уведомлений",
     *          @OA\JsonContent(
     *              ref=@Model(type=NotificationPreferences::class, groups={"Default"})
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Объект обновленных настроек уведомлений",
     *          @OA\JsonContent(
     *              ref=@Model(type=NotificationPreferences::class, groups={"Default"})
     *          )
     *     )
     * )
     */
    public function updateNotificationPreferences(NotificationPreferences $notificationPreferences)
    {
        /** @var User $user */
        $user = $this->getUser();

        $notificationReceiver = new NotificationReceiver();
        $notificationReceiver->setMuteDiscussion($notificationPreferences->getDisableDiscussionMessageNotifications());
        $notificationReceiver->setMutePrivate($notificationPreferences->getDisablePrivateMessageNotifications());
        $notificationReceiver->setNPersonsOid($user->getDbOid());

        /** @var NotificationPreferences $updated */
        $updatedPreferences = $this->notifierQueryService->updateReceiver($notificationReceiver);

        return $this->responseSuccessWithObject($updatedPreferences);
    }
}