<?php

declare(strict_types=1);

namespace Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="repository")
 */
class Repository
{
    /** @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private int $id;

    /** @ORM\Column(type="string")
     * @var string
     */
    private string $name;

    /** @ORM\ManyToMany(targetEntity="Repository", inversedBy="repositoryDependencies")
     * @ORM\JoinTable(name="repository_dependency")
     */
    private Collection $dependencies;

    /** INVERSE RELATIONSHIP
     *
     * @ORM\ManyToMany(targetEntity="Repository", mappedBy="dependencies")
     */
    private Collection $dependants;

    /**
     * @var int
     */
    private int $dependantsCount = 0;

    public function __construct(string $name) {
        $this->name = $name;

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
     * Adds a new dependant repository.
     *
     * @param Repository $repository
     */
    public function addDependant(Repository $repository) {
        $this->dependants[] = $repository;
        $this->dependantsCount++;
    }

    /**
     * Add a new dependency
     *
     * @param Repository $repository
     */
    public function addDependency(Repository $repository) {
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
     * Get list of dependant repositories
     * @return Repository[]
     */
    public function getDependants() {
        return $this->dependants->toArray();
    }

    /**
     * Returns a list of unique dependant repository names gathered recursively.
     *
     * @param string[] $prev_result
     *
     * @return string[]
     */
    public function getRecursiveDependantsList(?array $prev_result = []): array {
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
            $result = array_unique(array_merge($result, array_values($dependant->getRecursiveDependantsList($result))));
        }

        return $result;
    }
}