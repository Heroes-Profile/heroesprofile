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

It fails if an endpoint charging quota is missing either its fixture file or the
`api.fixtures` middleware, and separately if a row in `api_endpoints` has no route
behind it — the registry drives the pricing table and the docs nav, so a row with
no endpoint is something the portal advertises and the API answers 404 for.

The registry half needs a database and degrades to a warning without one, so the
route-to-fixture half still runs in CI.

### Creating

Never hand-write a fixture — the shape will be wrong. Capture the real response:

```
php artisan api:capture-samples --promote --endpoint=<key>
```

It calls the controller directly — no API key, no running server — and `--promote`
writes straight to `resources/api-fixtures/`. Without it the capture lands in
`storage/app/api-samples/` for inspection instead.

Endpoints needing input take it from `--query`, which also fills route parameters
and nested ones:

```
php artisan api:capture-samples --promote --endpoint=player --query="battletag=Someone#1234" --query=region=1
php artisan api:capture-samples --promote --endpoint=replay_data --query=replayID=64836717
php artisan api:capture-samples --promote --endpoint=talent_builder_replays --query=hero=Anduin --query="selectedtalents[1]=2859"
```

Quote the battletag — `#` starts a comment in PowerShell.

**`--promote` refuses to write a capture that should not become a fixture:** an
empty response, an internal validation failure (those arrive as HTTP 200 with a
`status` field, so nothing else catches them), or any 4xx from the endpoint
itself. It warns above 1MB — use `--rows=N` to keep the shape and drop the bulk.

Identifying fields are replaced as it writes: `battletag`, `split_battletag`,
`blizz_id`, `region`, `replayID` and `game_date`. The same original always maps to
the same fake, so records still cross-reference. **Read the replacement table it
prints.** A field it does not know is written through untouched — that is how a
real player name shipped once beside an anonymised battletag — so if an endpoint
returns player data and a field is missing from that table, add it to
`IDENTIFYING_FIELDS` in `CaptureApiSamples` and re-capture.

Replay ids matter especially: `/matches/{replayID}` is public, so a real one
resolves back to the match and undoes the battletag replacement.

## Adding a public API endpoint

Five steps, and two commands refuse to let you skip any of them.

**1. Wrap an existing controller.** Public endpoints are thin wrappers over the
controllers the site itself uses — no new SQL. Add a method to the matching
`app/Http/Controllers/Api/Public/*Controller`.

**Read the target controller's validation rules first.** Almost every one assumes
parameters the site's own pages always send, and none of them handle the absence
well — `game_type` alone is required-as-array in one, required-as-scalar in
another, and optional-meaning-everything in a third. A missing parameter usually
surfaces as HTTP 200 with a `status` field rather than an error. Declare defaults
for what the site always sends, and reject what has no sensible default with a
422 naming it.

**2. Route it** in `routes/api-public.php` with both middlewares, named
`api.public.*`:

```php
Route::get('players/heroes', [PlayerController::class, 'heroes'])
    ->middleware(['api.fixtures:player_hero_all', 'api.quota:player_hero_all'])
    ->name('api.public.players.heroes');
```

The middleware argument is the `api_endpoints.endpoint` key, which is also the
fixture filename.

**3. Document it** in `config/api_spec.php` — a `summary`, its `parameters` (or
`uses` to pull in the shared `globals` / `player` sets), and `async` if it can
answer 202. Add it to a section under `groups`. Response schemas come from the
fixture, so there is nothing to write for those; describe individual response
fields by adding them to `fields`, which applies by name across every endpoint.

**4. Capture its fixture**, as above.

**5. Rebuild the spec:**

```
php artisan api:build-spec
```

Then check both:

```
php artisan api:check-fixtures
php artisan api:build-spec --check
```

`api:check-fixtures` fails if a routed endpoint has no fixture, **and** if a
registry row has no route — the second half is what catches the pricing table and
docs nav advertising endpoints that 404. Rows retired on purpose are named in
`UNROUTED_BY_DESIGN`.

`api:build-spec` fails if an endpoint has no config entry, no section, or no
fixture to derive a response from. Both run in CI.

# Contributing

All contributions are welcome. The owners of Heroes Profile reserve the right to include or deny any merge requests from the community. Also, please try and only create pull requests that contain updates to the specific update you want to make. Including environment or auto-generated updates to framework code that are not required for your change only complicates making updates.

If a contribution requires changes to the database, or how the data is grabbed from replays, please log an issue report detailing your need.
