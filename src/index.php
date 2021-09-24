<?php
// Entry point
declare(strict_types=1);

use Discovery\Parser;
use Discovery\Updater;
use Discovery\Walker;
use Entities\Repository;
use Entities\RepositoryObservable;
use Service\RepositoryManager;

const PROJECTS_PATH = 'repositories';

require_once __DIR__ . "/../bootstrap.php";

$manager = new RepositoryManager($entityManager);
$updater = new Updater($manager);

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
            echo "Commit ID was expected as first argument along with commit";
            exit(1);
        }
        $commitId = $argv[2];

        if (empty($argv[3])) {
            echo "Repository name was expected as second argument along with commit";
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
            $foundRepository = new RepositoryObservable($foundRepository);
        }

        if ($foundRepository === null) {
            echo "Repository {$updatedRepositoryName} not found.";
            exit(1);
        }

        $dependants = $foundRepository->getRecursiveDependants();
        if (in_array('--update', $argv)) {
            foreach($dependants as $dependant) {
                $dependantRepository = new RepositoryObservable($manager->findRepository($dependant));
                $dependantRepository->updateComposer($updater);
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
            
            example: php ./index.php commit commitId repositoryName [--rediscover|--update]
            
            --rediscover \t\t will force the discovery of schema from disk instead of loading it from storage.
            --update \t\t will attempt to run composer update on each dependant repository. 
            \n
        HELP;


}

function createWalker(): Walker {
    // Initializes a walker to discover folders
    $walker = null;
    try {
        $walker = new Walker(PROJECTS_PATH);
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
        $repositoriesList[$repositoryName]['instance'] = new RepositoryObservable(new Repository($repositoryName, $composerFilePath));
        $repositoriesList[$repositoryName]['dependencies'] = $parser->getDependenciesFolderNames();
    }

    return $repositoriesList;
}