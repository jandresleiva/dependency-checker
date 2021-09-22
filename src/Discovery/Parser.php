<?php

declare(strict_types=1);

namespace Discovery;

class Parser
{
    /**
     * @var string
     */
    private string $repositoryName;

    /**
     * @var array
     */
    private array $dependenciesFolderNames;

    public function parseFileContent(string $fileContent): void
    {
        $this->parseData($fileContent);
    }

    public function parseFile($filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception("The given file {$filePath} does not exist.");
        }

        $fileContents = file_get_contents($filePath);
        if ($fileContents === false or empty($fileContents)) {
            throw new \Exception("The given file {$filePath} cannot be read or is empty.");
        }

        $this->parseData($fileContents);
    }

    /**
     * This method will ensure the file can be read and has valid Json format, and will extract the repository name
     * and dependencies folder names. Those can be obtained by getRepositoryName and getDependenciesFolderNames getters.
     *
     * @throws \Exception
     */
    private function parseData(string $fileContents): void
    {
        $repositoryData = json_decode($fileContents, true);
        if ($repositoryData === null) {
            throw new \Exception("The given file does not contain a valid JSON format.");
        }

        $this->dependenciesFolderNames = $this->extractDependenciesFolderNames($repositoryData['repositories']) ?? [];
        $this->repositoryName = $this->extractRepositoryName($repositoryData['name']) ?? '';
    }

    /**
     * This method will parse the repository name to extract the vendor and slashes.
     *
     * @param string $rawRepositoryName
     *
     * @return string
     */
    private function extractRepositoryName(string $rawRepositoryName): string
    {
        $nameParts = explode("/", $rawRepositoryName);
        return strtolower($nameParts[count($nameParts) - 1]);
    }

    /**
     * This method will parse the dependencies' folder names to extract the url components.
     *
     * @param array $rawDependenciesList
     *
     * @return array
     */
    public function extractDependenciesFolderNames(array $rawDependenciesList): array
    {
        $curatedDependencies = [];

        foreach ($rawDependenciesList as $rawDependency) {
            $urlParts = explode("/", $rawDependency["url"] ?? '');
            $folderName = pathinfo($urlParts[count($urlParts) - 1], PATHINFO_FILENAME);
            $curatedDependencies[] = strtolower($folderName);
        }

        return $curatedDependencies;
    }

    /**
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * @return array
     */
    public function getDependenciesFolderNames(): array
    {
        return $this->dependenciesFolderNames;
    }
}