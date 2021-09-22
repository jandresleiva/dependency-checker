<?php

namespace Service;

use Entities\Repository;

interface RepositoryManagerInterface
{
    /**
     * Inserts or updates a Repository
     *
     * @param Repository $entity
     */
    public function upsertRepository(Repository $entity): void;

    /**
     * Retrieves a repository that matches the given name
     *
     * @param string $repositoryName
     *
     * @return Repository|null
     */
    public function findRepository(string $repositoryName): ?Repository;

    /**
     * Executes the Storage Queries to persist the data.
     */
    public function flush(): void;
}