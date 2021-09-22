<?php
declare(strict_types=1);

use Entities\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    public function testAddDependantIncrementsDependantsCount(): void
    {
        $repoA = new Repository(uniqid("RepoA"));
        $repoB = new Repository(uniqid("RepoB"));
        $repoA->addDependant($repoB);

        $this->assertEquals(1, $repoA->getDependantsCount());
    }

    public function testGetDependencies(): void
    {
        $repoA = new Repository(uniqid("RepoA"));
        $repoB = new Repository(uniqid("RepoB"));

        $repoA->addDependency($repoB);
        $expect = [$repoB->getName()];
        $this->assertEquals($expect, $repoA->getDependencies());
    }

    public function testGetDependants(): void
    {
        $repoA = new Repository(uniqid("RepoA"));
        $repoB = new Repository(uniqid("RepoB"));

        $repoA->addDependency($repoB);
        $expect = [$repoA->getName()];
        $this->assertEquals($expect, $repoB->getDependants());
    }

    public function testGetRecursiveDependencies(): void
    {

        $repoA = new Repository(uniqid("RepoA"));
        $repoB = new Repository(uniqid("RepoB"));
        $repoC = new Repository(uniqid("RepoC"));
        $repoD = new Repository(uniqid("RepoD"));

        $repoA->addDependency($repoB);
        $repoB->addDependency($repoC);
        $repoA->addDependency($repoD);
        $expect = [$repoB->getName() => [$repoC->getName() => []], $repoD->getName() => []];
        $this->assertEquals($expect, $repoA->getRecursiveDependencies());
    }

    public function testGetRecursiveDependants(): void
    {

        $repoA = new Repository(uniqid("RepoA"));
        $repoB = new Repository(uniqid("RepoB"));
        $repoC = new Repository(uniqid("RepoC"));
        $repoD = new Repository(uniqid("RepoD"));

        $repoB->addDependency($repoC);
        $repoC->addDependency($repoD);
        $repoA->addDependency($repoD);

        $expect = [$repoA->getName(), $repoB->getName(), $repoC->getName(), $repoD->getName()];
        $this->assertEqualsCanonicalizing($expect, $repoD->getRecursiveDependants());
    }
}
