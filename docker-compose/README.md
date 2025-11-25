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

## Services

- **app**: Laravel application (port 8000)
- **mysql**: MySQL 8.0 database (port 3306)

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

The MySQL container automatically creates all required databases:

- heroesprofile (main)
- heroesprofile_globals
- heroesprofile_cache
- heroesprofile_logs
- heroesprofile_ngs
- heroesprofile_ccl
- heroesprofile_mcl
- heroesprofile_hi
- heroesprofile_hi_nc

Database credentials:

- Host: localhost:3306
- Username: heroesprofile
- Password: heroesprofile_password
- Root password: root_password
