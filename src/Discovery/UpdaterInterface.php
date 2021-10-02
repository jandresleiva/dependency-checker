<?php

namespace Discovery;

use Entities\RepositoryInterface;

interface UpdaterInterface
{
    public function updateComposer(RepositoryInterface $repository): array;
}