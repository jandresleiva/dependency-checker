<?php

namespace Entities;

use SplObserver;
use SplSubject;

interface RepositoryInterface
{

    public function getName(): string;
    public function getFilePath(): string;
    public function getDirty(): bool;
    public function setDirty(?bool $isDirty = true): void;
    public function getRecursiveDependencies(): array;
    public function getRecursiveDependants(?array $prev_result = []): array;
    public function getDependantsCount(): int;
    public function getDependants(): array;
    public function getDependencies(): array;
    public function addDependency(RepositoryInterface $repository): void;
    public function addDependant(RepositoryInterface $repository): void;
}