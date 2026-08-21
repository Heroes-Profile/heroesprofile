# Heroes Profile
[Master Heroes Profile](https://www.heroesprofile.com/)(master branch)


# Cloning the Heroes Profile repository

-   `git clone --recursive https://github.com/Heroes-Profile/heroesprofile.git`

## Installation

Heroes Profile is a Laravel, Vue3, Tailwind app compiled with Vite. Making use of a MySql database. Every system has different methods for getting the required dependencies installed so please reference the main tools sites for installation instructions.

## Local Docker Installation Instructions

[Local Docker Installation Instructions](docker-compose/README.md)

## Local Installation Instructions

Laravel - https://laravel.com/

PHP - PHP can be installed in different ways. If you do not currently have PHP installed, use google to find the best method for you.

MySQL 8.4 - MySQL can be installed in different ways. If you do not currently have MySQL installed, use google to find the best method for you. The Docker setup uses MySQL 8.4.

Optional - A visual tool for looking at the database and data is suggested. We use [DBeaver](https://dbeaver.io/) (MySQL Workbench does not work well with MySQL 8.4).

In addition to creating the environment yourself, there are also a lot of different tools that can pull together all the dependencies for you. Homestead is an example, or our docker instructions referenced above.

For windows users, Wampserver64 is a useful tool as it installs the MySql server and php at the same time. https://sourceforge.net/projects/wampserver/

## Project Setup

-   From the command line, navigate to the heroesprofile repository.
-   Configure `.env` file using `.env.example`
-   Run `npm install`
-   Run `composer install`
-   Run `php artisan key:generate` make sure the APP_KEY has this value in the .env file

## Database setup

-   Create the following schemas in your MySql database: `heroesprofile`, `heroesprofile_globals`, `heroesprofile_cache`, `heroesprofile_logs`, `heroesprofile_ngs`, `heroesprofile_ccl`, `heroesprofile_mcl`, `heroesprofile_hi`, `heroesprofile_hi_nc`

-   Base site table migrations and seeders have been provided.  Run `php artisan migrate` to run migrations and `php artisan db:seed`.  It seems the seeders stop early sometimes, or error out on memory issues.  If that occurs, just comment out the seeders that have already ran in `database\seeders\DatabaseSeeder.php` and then run `php artisan db:seed` again.  You can also attempt to increase memory usage/and or execution timeout.  E.g  `php -d memory_limit=2G -d max_execution_time=0 artisan db:seed `

NOTE:  The data provided in the seeders is not complete.  A lot of player data maps to battletag ZEMILL#1940 and global data based on patch `2.55.4.91418`

## Running the project

-   From the command line, navigate to the heroesprofile repository.
-   Run `php artisan serve` - spins up the webserver
-   The path to paste into the browser will show up in the command line.
-   From a second command line, navigate to the heroesprofile repository.
-   Run `npm run dev` - - watches for any changes and automatically recompiles


## Public API fixtures

Endpoints under `/v1/*` serve a canned response instead of live data whenever an
account has not activated live data or has test mode switched on. Those canned
responses are the JSON files in `resources/api-fixtures/`, one per endpoint, named
after its key in `api_endpoints.endpoint` — so `heroes_stats` is
`heroes_stats.json`. `replay_download` returns a file rather than JSON, so its
fixture is a `.StormReplay`.

**Every public endpoint needs one.** Without a fixture an unactivated account
would fall through to live data, which is the thing the gate exists to prevent.

### Checking

-   Run `php artisan api:check-fixtures`

It walks the routes, and fails if an endpoint charging quota is missing either its
fixture file or the `api.fixtures` middleware. Run it after adding an endpoint.

### Creating

Do not hand-write a fixture — the shape will be wrong. Capture the real response
and edit that:

-   Run `php artisan api:capture-samples --endpoint=<key>`

It calls the controller directly (no API key or running server needed) and writes
the response to `storage/app/api-samples/<key>.json`. Endpoints needing input take
it from `--query`, which also fills route parameters:

-   `php artisan api:capture-samples --endpoint=player --query=battletag=Someone#1234 --query=region=1`
-   `php artisan api:capture-samples --endpoint=replay_data --query=replayID=64836717`

`battletag`, `blizz_id` and `region` are replaced with stable fakes as it writes —
the same original always maps to the same fake, so records still cross-reference.
The command prints what it replaced; if an endpoint returns player data and that
table is empty, it came back under a field name the scrubber does not know, and
that field needs adding to `IDENTIFYING_FIELDS` before the sample is used.

Then copy the sample into `resources/api-fixtures/` and edit it:

-   Replace real statistics with round placeholder numbers. Nobody should mistake
    a fixture for production data.
-   Keep hero, map, talent and game type records real — they are public game data,
    and consumers match against them.
-   Keep the cardinality the endpoint actually returns: ten players in a match,
    three bans a team, seven talent levels. Trim only open-ended lists, and trim
    them to a handful rather than one.
-   Do not add fields that are not in the live response. A fixture must be
    shape-identical, or code written against it breaks on activation.

# Contributing

All contributions are welcome. The owners of Heroes Profile reserve the right to include or deny any merge requests from the community. Also, please try and only create pull requests that contain updates to the specific update you want to make. Including environment or auto-generated updates to framework code that are not required for your change only complicates making updates.

If a contribution requires changes to the database, or how the data is grabbed from replays, please log an issue report detailing your need.
