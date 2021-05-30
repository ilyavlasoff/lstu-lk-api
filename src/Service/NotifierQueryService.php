<?php

namespace App\Service;

use App\Model\ExternalConsumingParam\NotificationReceiver;
use App\Model\ExternalConsumingParam\NotificationReceiverDevice;
use App\Model\QueryParam\NotificationPreferences;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NotifierQueryService extends AbstractQueryService
{
    private $urlBase;

    public function __construct(HttpClientInterface $httpClient, SerializerInterface $serializer, ParameterBagInterface $parameterBag)
    {
        parent::__construct($httpClient, $serializer);
        $this->urlBase = $parameterBag->get('notifier_base_url');
    }

    public function addUserDevice(NotificationReceiverDevice $device) {
        $urlPath = 'management/device';

        $deviceSerialized = $this->serializer->serialize($device, 'json');

        $response = $this->makeQuery($this->urlBase, $urlPath, 'POST', 'http', 200,
            [], [], [], $deviceSerialized, '', false, '', true);

        return $response['success'];
    }

    public function removeUserDevice(NotificationReceiverDevice $device) {
        $urlPath = 'management/device';

        $deviceSerialized = $this->serializer->serialize($device, 'json');

        $response = $this->makeQuery($this->urlBase, $urlPath, 'DELETE', 'http', 200,
            [], [], [], $deviceSerialized, '', false, '', true);

        return $response['success'];
    }

    public function getReceiver(string $user) {
        $urlPath = 'management/receiver';

        return $this->makeQuery($this->urlBase, $urlPath, 'GET', 'http', 200,
            ['receiver' => $user], [], [], null, '', true, NotificationReceiver::class, false);

    }

    public function addReceiver(NotificationReceiver $receiver) {
        $urlPath = 'management/receiver';

        $deviceSerialized = $this->serializer->serialize($receiver, 'json');

        $response = $this->makeQuery($this->urlBase, $urlPath, 'POST', 'http', 200,
            [], [], [], $deviceSerialized, '', false, '', true);

        return $response['success'];
    }

    public function updateReceiver(NotificationReceiver $receiver) {
        $urlPath = 'management/receiver';

        $deviceSerialized = $this->serializer->serialize($receiver, 'json');

        return $this->makeQuery($this->urlBase, $urlPath, 'PATCH', 'http', 200,
            [], [], [], $deviceSerialized, '', true, NotificationPreferences::class, false);

    }

    public function removeReceiver(NotificationReceiver $receiver) {
        $urlPath = 'management/receiver';

        $deviceSerialized = $this->serializer->serialize($receiver, 'json');

        $response = $this->makeQuery($this->urlBase, $urlPath, 'DELETE', 'http', 200,
            [], [], [], $deviceSerialized, '', false, '', true);

        return $response['success'];
    }
}