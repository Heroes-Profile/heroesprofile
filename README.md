# Heroes Profile
[Master Heroes Profile](https://www.heroesprofile.com/)(master branch)


# Cloning the Heroes Profile repository

-   `git clone --recursive https://github.com/Heroes-Profile/heroesprofile.git`

## Local Docker Installation Instructions

[Local Docker Installation Instructions](docker-compose/README.md)

## Installation

Heroes Profile is a Laravel, Vue3, Tailwind app compiled with Vite. Making use of a MySql database. Every system has different methods for getting the required dependencies installed so please reference the main tools sites for installation instructions.

Laravel - https://laravel.com/

PHP - PHP can be installed in different ways. If you do not currently have PHP installed, use google to find the best method for you.

MySQL - MySQL can be installed in different ways. If you do not currently have MySQL installed, use google to find the best method for you.

Optional - A visual tool for looking at the database and data is suggested. MySql Workbench is our preference - https://www.mysql.com/products/workbench/

In addition to creating the environment yourself, there are also a lot of different tools that can pull together all the dependencies for you. Homestead is an example.

For windows users, Wampserver64 is a useful tool as it installs the MySql server and php at the same time. https://sourceforge.net/projects/wampserver/

## Project Setup

-   From the command line, navigate to the heroesprofile repository.
-   Configure `.env` file using `.env.example`
-   Run `npm install`
-   Run `composer install`
-   Run `php artisan key:generate` make sure the APP_KEY has this value in the .env file

## Database setup

-   Create the following schemas in your MySql database. `heroesprofile`, `heroesprofile_cache`, `heroesprofile_logs`, 

-   Base site table migrations and seeders have been provided.  Run `php artisan migrate` to run migrations and `php artisan db:seed`.  It seems the seeders stop early sometimes, or error out on memory issues.  If that occurs, just comment out the seeders that have already ran in `database\seeders\DatabaseSeeder.php` and then run `php artisan db:seed` again.  NOTE:  The data provided in the seeders is not complete.  A lot of player data maps to battletag ZEMILL#1940 and global data based on patch `2.55.4.91418`

## Running the project

-   From the command line, navigate to the heroesprofile repository.
-   Run `php artisan serve` - spins up the webserver
-   The path to paste into the browser will show up in the command line.
-   From a second command line, navigate to the heroesprofile repository.
-   Run `npm run dev` - - watches for any changes and automatically recompiles


# Contributing

All contributions are welcome. The owners of Heroes Profile reserve the right to include or deny any merge requests from the community. Also, please try and only create pull requests that contain updates to the specific update you want to make. Including environment or auto-generated updates to framework code that are not required for your change only complicates making updates.

If a contribution requires changes to the database, or how the data is grabbed from replays, please log an issue report detailing your need.
