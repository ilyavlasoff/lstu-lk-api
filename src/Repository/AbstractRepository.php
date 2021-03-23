<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }
}