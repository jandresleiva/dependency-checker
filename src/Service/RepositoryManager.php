<?php
// Data access layer.

declare(strict_types=1);

namespace Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Entities\Repository;
use Entities\RepositoryInterface;

class RepositoryManager implements RepositoryManagerInterface
{
    public const DOMAIN_NAME = 'Entities\Repository';

    private EntityManagerInterface $entityManager;

    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(self::DOMAIN_NAME);
    }

    /**
     * @inheritDoc
     */
    public function upsertRepository(RepositoryInterface $entity): void {
        $instance = $this->entityRepository->findOneBy(array('name' => $entity->getName())) ?? $entity;

        $this->entityManager->persist($instance);
    }

    /**
     * @inheritDoc
     */
    public function findRepository(string $repositoryName): ?Repository {
        return $this->entityRepository->findOneBy(array('name' => $repositoryName));
    }

    /**
     * @inheritDoc
     */
    public function flush(): void {
        $this->entityManager->flush();
    }
}