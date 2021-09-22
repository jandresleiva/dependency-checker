# Dependency Checker

## Requirements
This project requires `php7.4`, `docker` and `composer` installed on the local machine.

## Instructions
You will need to make our scripts executable:
`sudo chmod +x initialize` and `sudo chmod +x cleanup`

To get the environment running start the docker mysql image:

`run ./initialize`

Then Run the migrations:

`run migrations vendor/bin/doctrine-migrations --no-interaction migrate`

And to remove the containers once you're done testing run:
`run ./cleanup`

### Configuration
Now there's a base db.json within Secrets folder, which works with the default mysql container. If you're using this for your custom mysql container, you'd need to update the connectors there (**NOTE:** Avoid commiting it to github).

You will also need to replace the const in index.php for the destination folder of your repositories.

TODO: In the future this could be part of the base configuration.

### Testing
To run tests from root run:
`vendor/bin/phpunit Tests/`

## Usage
To run this command run from root folder `php src/index.php [args]`
php ./index.php [command] [arguments] [options] \n

    discover
    --------
    Will iterate over the base directory and attempt to build dependencies for the composer.json files.
    It will show the built tree before exit.
    
    example: php ./index.php discover [--persist]
    
    --persist   will make the iteration persistent through the database. If repositories already exist, will update them.  
    
    commit
    ------
    Will determine which dependant repositories need to get updated after this commit. It takes two arguments commitId and repositoryName.
    By default this will get the saved schema from the storage.
    
    example: php ./index.php commit commitId repositoryName [--rediscover|--update]
    
    --rediscover    will force the discovery of schema from disk instead of loading it from storage.
    --update        will attempt to run composer update on each dependant repository.