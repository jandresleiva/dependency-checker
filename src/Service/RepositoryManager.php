<?php
// Data access layer.

declare(strict_types=1);

namespace Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Entities\Repository;

class RepositoryManager
{
    public const DOMAIN_NAME = 'Entities\Repository';

    private EntityManagerInterface $entityManager;

    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(self::DOMAIN_NAME);
    }

    public function upsertRepository(Repository $entity): void {
        $instance = $this->entityRepository->findOneBy(array('name' => $entity->getName())) ?? $entity;

        $this->entityManager->persist($instance);
    }

    public function findRepository(string $repositoryName): ?Repository {
        return $this->entityRepository->findOneBy(array('name' => $repositoryName));
    }

    public function flush(): void {
        $this->entityManager->flush();
    }
}