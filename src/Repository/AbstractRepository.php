<?php

namespace App\Repository;

use App\Exception\DataAccessException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }

    protected function getNewOid(): String
    {
        $stm = $this->entityManager->getConnection()->prepare('SELECT GET_NEW_OID() AS OID FROM DUAL');

        $success = $stm->execute();
        if(!$success) {
            throw new DataAccessException();
        }

        return $stm->fetchOne();
    }
}