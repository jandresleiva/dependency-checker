<?php

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

    public function __construct() {
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
     * @return Repository[]
     */
    public function getDependencies(): array {
        return $this->dependencies->toArray();
    }

    /**
     * Get list of dependant repositories
     * @return Repository[]
     */
    public function getDependants() {
        return $this->dependants->toArray();
    }
}