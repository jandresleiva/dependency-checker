<?php

declare(strict_types=1);

namespace Discovery;

class Walker
{
    /**
     * @var string
     */
    private string $baseDirectory;

    /**
     * @var string[]
     */
    private array $directories;

    /**
     * @var string[]
     */
    private array $composerPaths;

    public function __construct(string $baseDirectory)
    {
        if (!file_exists( $baseDirectory)) {
            throw new \Exception("The given directory $baseDirectory for the walker does not exist.");
        }

        $this->baseDirectory = $baseDirectory;
    }

    public function getDirectories(): array {
        return $this->directories;
    }

    /**
     * Walks within the baseDirectory and gathers the folder names.
     *
     * @return string[]
     */
    public function walk(): array
    {
        $scan = glob($this->baseDirectory . "/*");

        $this->directories = array_filter($scan, function($el) {
            return is_dir($el);
        });

        return $this->directories;
    }

    /**
     * Iterates within each directory, and finds the composer.json path if any.
     * The results are stored in the composerPaths property.
     *
     * @return array
     */
    public function extractComposerFiles(): array
    {
        $this->walk();

        foreach ($this->directories as $repositoryDirectory) {
            $directory = new \RecursiveDirectoryIterator($repositoryDirectory);
            $iterator = new \RecursiveIteratorIterator($directory);

            foreach($iterator as $file)
            {
                $fileName = (string) $file;
                $fileNameParts = explode("/", $fileName);

                if ($fileNameParts[count($fileNameParts)-1] == "composer.json") {
                    $this->composerPaths[] = $fileName;
                    break;
                }
            }
        }
        return $this->composerPaths;
    }

    /**
     * After walking through the base path, iterates over each sub-folder to find the composer.json files.
     *
     * @return \Iterator Iterator of file paths to the composer.json files.
     */
    public function extractComposerFilesGenerator(): \Iterator
    {
        $this->walk();

        foreach ($this->directories as $repositoryDirectory) {
            $directory = new \RecursiveDirectoryIterator($repositoryDirectory);
            $iterator = new \RecursiveIteratorIterator($directory);

            foreach($iterator as $file)
            {
                $fileName = (string) $file;
                $fileNameParts = explode("/", $fileName);

                if ($fileNameParts[count($fileNameParts)-1] == "composer.json") {
                    yield $fileName;
                    break;
                }
            }
        }
    }

}