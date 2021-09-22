<?php
// Entry point
declare(strict_types=1);

use Discovery\Parser;
use Discovery\Walker;
use Entities\Repository;
use Service\RepositoryManager;

require_once __DIR__ . "/../bootstrap.php";

$manager = new RepositoryManager($entityManager);

switch ($argv[1]) {
    case 'discover':
        $walker = createWalker();

        $repositoriesList = [];

        $mustPersist = false;
        if (in_array("--persist", $argv)) {
            $mustPersist = true;
        }

        $repositoriesList = getRepositoriesList($walker);

        foreach ($repositoriesList as $repository) {
            if ($mustPersist) {
                $manager->upsertRepository($repository['instance']);
            }

            foreach ($repository['dependencies'] as $dependencyName) {
                if (in_array($dependencyName, array_keys($repositoriesList))) {
                    $repository['instance']->addDependency($repositoriesList[$dependencyName]['instance']);
                }
            }
        }

        if ($mustPersist) {
            $manager->flush();
        }

        $tree = [];
        foreach ($repositoriesList as $updatedRepositoryName => $repository) {
            if ($repository['instance']->getDependantsCount() == 0) {
                $tree[$updatedRepositoryName] = $repository['instance']->getRecursiveDependencies();
            }
        }
        var_dump($tree);
        break;

    case 'commit':
        $walker = createWalker();


        if (empty($argv[2])) {
            echo "Commit ID must be provided along with --commit";
            exit(1);
        }
        $commitId = $argv[2];

        if (empty($argv[3])) {
            echo "Commit ID must be provided along with --commit";
            exit(1);
        }
        $updatedRepositoryName = $argv[3];

        $repositoriesList = getRepositoriesList($walker);

        if (in_array('--rediscover', $argv)) {
            $foundRepository = null;
            foreach ($repositoriesList as $repositoryName => $repository) {
                foreach ($repository['dependencies'] as $dependencyName) {
                    if (in_array($dependencyName, array_keys($repositoriesList))) {
                        $repository['instance']->addDependency($repositoriesList[$dependencyName]['instance']);
                    }
                }
                if ($repositoryName == $updatedRepositoryName) {
                    $foundRepository = $repository['instance'];
                }
            }
        } else {
            $foundRepository = $manager->findRepository($updatedRepositoryName);
        }

        if ($foundRepository === null) {
            echo "Repository {$updatedRepositoryName} not found.";
            exit(1);
        }

        $dependants = $foundRepository->getRecursiveDependants();
        if (in_array('--update', $argv)) {
            foreach($dependants as $dependant) {
                $dependantRepository = $manager->findRepository($dependant);
                if ($dependantRepository->getDirty() === true) {
                    echo "Repository {$dependant} is already being updated by another process.\n";
                    continue;
                }

                // Implements a lock mechanism to avoid running update while it's running.

                $dependantRepository->setDirty();
                $manager->flush();

                chdir(dirname($repositoriesList[$dependant]['composerFilePath']));
                exec("composer update");

                $dependantRepository->setDirty(false);
                $manager->flush();
            }
        }

        var_dump($dependants);

        break;


    case '--help':
    default:
        echo <<<HELP
            Use a valid option. Usage:\n
            php ./index.php [command] [arguments] [options] \n
            
            discover
            --------
            Will iterate over the base directory and attempt to build dependencies for the composer.json files.
            It will show the built tree before exit.
            
            example: php ./index.php discover [--persist]
            
            --persist \t\t will make the iteration persistent through the database. If repositories already exist, will update them.  
            
            commit
            ------
            Will determine which dependant repositories need to get updated after this commit. It takes two arguments commitId and repositoryName.
            By default this will get the saved schema from the storage.
            
            example: php ./index.php commit commitId repositoryName [--rediscover]
            
            --rediscover \t\t will force the discovery of schema from disk instead of loading it from storage. 
            \n
        HELP;


}

function createWalker(): Walker {
    // Initializes a walker to discover folders
    $walker = null;
    try {
        $walker = new Walker(__DIR__ . "/../DirectorioPrueba");
    } catch (\Exception $e) {
        echo $e->getMessage();
        exit(1);
    }
    return $walker;
}

function getRepositoriesList(Walker $walker): array {
    $repositoriesList = [];

    foreach ($walker->extractComposerFilesGenerator() as $composerFilePath) {
        try {
            $parser = new Parser();
            $parser->parseFile($composerFilePath);
        } catch (\Exception $e) {
            echo $e->getMessage();
            continue;
        }

        // I need to instantiate my repos first, to build dependencies.

        $repositoryName = $parser->getRepositoryName();
        $repositoriesList[$repositoryName]['composerFilePath'] = $composerFilePath;
        $repositoriesList[$repositoryName]['instance'] = new Repository($repositoryName);
        $repositoriesList[$repositoryName]['dependencies'] = $parser->getDependenciesFolderNames();
    }

    return $repositoriesList;
}