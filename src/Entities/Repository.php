<?php

declare(strict_types=1);

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="repository")
 */
class Repository implements RepositoryInterface
{
    /** @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $id;

    /** @ORM\Column(type="string")
     * @var string
     */
    protected string $name;

    /** @ORM\Column(type="boolean", options={"default":"0"})
     * @var bool
     */
    protected bool $isDirty = false;

    /** @ORM\Column(type="string")
     * @var string
     * Absolute path to the repositories' composer file
     */
    protected string $filePath;

    /** @ORM\ManyToMany(targetEntity="Repository", inversedBy="repositoryDependencies")
     * @ORM\JoinTable(name="repository_dependency")
     */
    protected Collection $dependencies;

    /** INVERSE RELATIONSHIP
     *
     * @ORM\ManyToMany(targetEntity="Repository", mappedBy="dependencies")
     */
    protected Collection $dependants;

    /**
     * @var int
     */
    protected int $dependantsCount = 0;


    public function __construct(string $name, string $filePath) {
        $this->name = $name;
        $this->filePath = $filePath;

        $this->dependencies = new ArrayCollection();
        $this->dependants = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * @return bool
     */
    public function getDirty(): bool
    {
        return $this->isDirty;
    }

    /**
     * @param bool|null $isDirty
     */
    public function setDirty(?bool $isDirty = true): void
    {
        $this->isDirty = $isDirty ?? true;
    }

    /**
     * Adds a new dependant repository.
     *
     * @param Repository $repository
     */
    public function addDependant(RepositoryInterface $repository): void {
        $this->dependants[] = $repository;
        $this->dependantsCount++;
    }

    /**
     * Add a new dependency
     *
     * @param Repository $repository
     */
    public function addDependency(RepositoryInterface $repository): void {
        $this->dependencies->add($repository);
        $repository->addDependant($this);
    }
    /**
     * Get list of dependencies
     *
     * @return string[]
     */
    public function getDependencies(): array {
        return array_map(function(Repository $dependency) {
            return $dependency->getName();
        }, $this->dependencies->toArray());
    }

    /**
     * Returns a tree of dependencies for this repository.
     *
     * @return string[]
     */
    public function getRecursiveDependencies(): array {
        $result = [];
        foreach($this->dependencies as $dependency) {
            $result[$dependency->getName()] = $dependency->getRecursiveDependencies();
        }

        return $result;
    }

    /**
     * Get list of dependant repositories
     *
     * @return string[]
     */
    public function getDependants(): array {
        return $this->dependants->toArray();
    }

    public function getDependantsCount(): int {
        return $this->dependantsCount;
    }

    /**
     * Returns a list of unique dependant repository names gathered recursively. Note that this will include itself.
     *
     * @param null|string[] $prev_result
     *
     * @return string[]
     */
    public function getRecursiveDependants(?array $prev_result = []): array {
        // If this is the first call, I'll initialize with this name, to avoid circle references.
        if (empty($prev_result)) {
            $result = [$this->getName()];
        } else {
            // Otherwise, I will make sure this is not already on the list.
            $result = $prev_result;
            if (in_array($this->getName(), $prev_result)) {
                return $result;
            }
        }

        $result[] = $this->getName();

        foreach( $this->dependants as $dependant) {
            $result = array_unique(array_merge($result, array_values($dependant->getRecursiveDependants($result))));
        }

        return array_unique($result);
    }
}
