# Jira Cycle Time

Use this Laravel PHP based repository to understand the cycle time within a Jira project.
This is based on status categories within Jira.

## How is the data stored

Data for all Jira tickets is stored locally, to enable faster evaluations.

## How do I run the project

This is a CLI based project. The available artisan commands can be listed by `php artisan cycletime`;

## How to get data

Ensure docker is running locally, and then run the following commands.

If you have run the project before, start at `step 9`.
These instructions are assuming you have never run this project previously.

1. `docker compose run --rm php composer install`
2. `docker compose run --rm php composer run-script post-root-package-install`
3. Set up all the needed values in your `.env` file.
4. `docker compose up -d pgsql`
5. `docker compose run --rm php php artisan migrate`
6. `docker compose up -d`
7. `docker compose exec php sh`
8. `php artisan cycletime:test`
9. `php artisan cycletime:issues`
10. `php artisan cycletime:calculate`
11. `php artisan cycletime:display`
