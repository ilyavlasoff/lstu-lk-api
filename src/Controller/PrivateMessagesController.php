<?php

namespace App\Controller;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PrivateMessagesController
 * @package App\Controller
 * @Route("/api/v1/messenger")
 */
class PrivateMessagesController extends AbstractRestController
{
    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct($serializer);
    }

    /**
     * @Route("/list", name="private_messages_list", methods={"GET"})
     */
    public function getPrivateMessageList()
    {

    }

    /**
     * @Route("/add", name="add_new_message", methods={"GET"})
     */
    public function addNewPrivateMessage()
    {

    }
}