<?php

namespace Discovery;

use Entities\RepositoryInterface;
use Service\RepositoryManagerInterface;

class Updater implements UpdaterInterface
{
    /**
     * @var RepositoryManagerInterface
     */
    protected RepositoryManagerInterface $entityManager;

    public function __construct(RepositoryManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Changes dir to the repo path and runs composer update if it's not dirty (already being updated).
     *
     * @param RepositoryInterface $repository
     *
     * @return array The system response
     */
    public function updateComposer(RepositoryInterface $repository): array
    {
        $response = [];
        if ($repository->getDirty() === true) {
            echo "Repository {$repository->getName()} is already being updated by another process.\n";
        } else {
            $repository->setDirty();
            $this->entityManager->flush();

            $actualDir = getcwd();

            chdir(dirname($repository->getFilePath()));
            #exec("touch here");
            exec("composer update --no-interaction", $response);
            chdir($actualDir);

            $repository->setDirty(false);
            $this->entityManager->flush();
        }
        return $response;
    }
}