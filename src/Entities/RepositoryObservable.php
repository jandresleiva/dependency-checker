<?php

namespace Entities;

use Discovery\UpdaterInterface;
use SplObjectStorage;
use SplObserver;
use SplSubject;

class RepositoryObservable implements RepositoryInterface, SplObserver, SplSubject
{
    private SplObjectStorage $dependantObservers;

    private RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->dependantObservers = new SplObjectStorage();

        $this->repository = $repository;
        foreach($this->repository->getDependants() as $dependant) {
            $this->attach(new RepositoryObservable($dependant));
        }
    }

    public function getDependants(): array
    {
        return $this->repository->getDependants();
    }
    public function getDependencies(): array
    {
        return $this->repository->getDependencies();
    }
    public function addDependency(RepositoryInterface $repository): void
    {
        $this->repository->addDependency($repository);
    }
    public function addDependant(RepositoryInterface $repository): void
    {
        $this->repository->addDependant($repository);
    }

    public function update(SplSubject $subject)
    {
        echo "updating {$this->repository->getName()}\n";
    }

    public function attach(SplObserver $observer)
    {
        $this->dependantObservers->attach($observer);
    }

    public function detach(SplObserver $observer)
    {
        $this->dependantObservers->detach($observer);
    }

    public function updateComposer(?UpdaterInterface $updater = null)
    {
        //Runs all the composer update stuff
        echo "Updating composer for {$this->repository->getName()}...\n";
        if ($updater !== null) {
            var_dump($this->repository->getFilePath());
            $updater->updateComposer($this);
        }
        $this->notify($updater);
    }
    public function notify(?UpdaterInterface $updater = null)
    {
        foreach ($this->dependantObservers as $observer) {
            echo "notify {$observer->getName()} observer\n";
            $observer->updateComposer($updater);
        }
    }

    public function getName(): string
    {
        return $this->repository->getName();
    }

    public function getFilePath(): string
    {
        return $this->repository->getFilePath();
    }

    public function getDirty(): bool
    {
        return $this->repository->getDirty();
    }

    public function setDirty(?bool $isDirty = true): void
    {
        $this->repository->setDirty($isDirty);
    }

    public function getRecursiveDependencies(): array
    {
        return $this->repository->getRecursiveDependencies();
    }

    public function getRecursiveDependants(?array $prev_result = []): array
    {
        return $this->repository->getRecursiveDependants($prev_result);
    }

    public function getDependantsCount(): int
    {
        return $this->repository->getDependantsCount();
    }
}