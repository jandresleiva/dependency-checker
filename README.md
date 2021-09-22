

# Instructions
You will need to make our scripts executable:
`sudo chmod +x initialize` and `sudo chmod +x cleanup`

To get the environment running start the docker mysql image:

`run ./initialize`

Then Run the migrations:

`run migrations vendor/bin/doctrine-migrations --no-interaction migrate`

And to remove the containers once you're done testing run:
`run ./cleanup`

## Testing
To run tests from root run:
`vendor/bin/phpunit Tests/`