# Docker Development Setup

This guide helps you set up Heroes Profile for local development using Docker Compose.

## Quick Start

1. **Clone the repository**

   ```bash
   git clone --recursive https://github.com/Heroes-Profile/heroesprofile.git
   cd heroesprofile/docker-compose
   ```

2. **Start the development environment**

   ```bash
   docker compose up -d
   ```

3. **Run database seeders (optional)**
   Wait for the app to create database tables before running seeders.
   
   ```bash
   docker compose exec app php artisan db:seed
   ```

    Sometimes seeders take awhile or use a lot of memory to run and can cause the
    seeding process to stop early. If this occurs you can either manually comment
    out seeders that have completed and run again, or attempt to increase memory
    usage and/or execution timeout.  E.g

    ```bash
    docker compose exec app php -d memory_limit=2G -d max_execution_time=0 artisan db:seed
    ```

    NOTE:  The data provided in the seeders is not complete.
    A lot of player data maps to battletag ZEMILL#1940 and
    global data based on patch `2.55.4.91418`

4. **Access the application**
   - [http://localhost:8000](http://localhost:8000)

## The API site

The API portal and the public API run from this same container — there is no
second service to start. The portal is at
[http://localhost:8000/Api](http://localhost:8000/Api) and the API answers under
`http://localhost:8000/api/external/v1`.

`ApiAccountsSeeder` creates five accounts, one per state the API branches on, and
prints their credentials when it runs. Each has a fixed API key, because keys are
hashed and shown once — a seeded account would otherwise be impossible to call
with. The password is `password` for all of them.

| Account | Resolves as |
| --- | --- |
| `fixtures@heroesprofile.test` | No plan, not migrated — serves canned fixture data |
| `basic@heroesprofile.test` | Basic plan, live data, low weekly quotas |
| `developer@heroesprofile.test` | Developer plan, live data, higher rate limit |
| `partner@heroesprofile.test` | Comped Partner grant, no subscription |
| `admin@heroesprofile.test` | Developer plan plus portal admin |

Calling it:

```bash
curl -H "Authorization: Bearer developer_local_api_key_000000000000000000000000000000000000"   "http://localhost:8000/api/external/v1/heroes"
```

That seeder refuses to run outside a local environment, and refuses again if
`heroesprofile_api.users` already holds rows — the keys above are public, so they
must never reach a database with real accounts in it.

Two things do not work locally, by design:

- **Billing.** `STRIPE_*` is blank in `.env.docker`. Subscribing and the Stripe
  webhook need real test-mode keys; everything else, including quota and
  entitlement, works from the seeded subscriptions.
- **The legacy uploader paths.** Those are scoped to `API_PUBLIC_DOMAIN`
  (`api.heroesprofile.com`), so they never match on localhost. Do not point that
  variable at localhost to test them — the same route file ends in a catch-all
  redirect to the live site, which would swallow every other page.

## Services

- **app**: Laravel application (port 8000)
- **mysql**: MySQL 8.4 database (port 3306)

## Useful Commands

```bash
# View application logs
docker compose logs -f app

# Access application container shell
docker compose exec app bash

# Run Laravel commands
docker compose exec app php artisan migrate

# Stop all services
docker compose down

# Rebuild containers
docker compose up --build

# Access MySQL CLI
docker compose exec mysql mysql -h mysql -u root -proot_password heroesprofile
```

## Database Access

The MySQL 8.4 container automatically creates all required databases:

- heroesprofile (main)
- heroesprofile_api
- heroesprofile_globals
- heroesprofile_cache
- heroesprofile_logs
- heroesprofile_ngs
- heroesprofile_ccl
- heroesprofile_mcl
- heroesprofile_hi
- heroesprofile_hi_nc
- heroesprofile_ml

Database credentials:

- Host: localhost:3306
- Username: heroesprofile
- Password: heroesprofile_password
- Root password: root_password

### GUI client (DBeaver)

We use [DBeaver](https://dbeaver.io/) to connect to the database. MySQL Workbench does not work well with MySQL 8.4.

1. Create a new **MySQL** connection in DBeaver
2. Use the credentials above (`localhost`, port `3306`, user `heroesprofile`)
3. After connecting, expand the connection to browse the databases listed above
