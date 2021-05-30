<?php

namespace App\Controller;

use App\Document\User;
use App\Model\ExternalConsumingParam\NotificationReceiver;
use App\Model\ExternalConsumingParam\NotificationReceiverDevice;
use App\Model\QueryParam\NotificationPreferences;
use App\Model\QueryParam\NotifiedDevice;
use App\Service\NotifierQueryService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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