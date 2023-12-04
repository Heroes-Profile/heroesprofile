# Heroes Profile
[Master Heroes Profile](https://rewrite.heroesprofile.com/)(master branch)

This public repository is the site re-write for [Heroes Profile](https://www.heroesprofile.com/) and is not currently in production.

# Cloning the Heroes Profile repository

-   `git clone --recursive https://github.com/Heroes-Profile/heroesprofile.git`

## Installation

Heroes Profile is a Laravel, Vue3, Tailwind app compiled with Vite. Making use of a MySql database. Every system has different methods for getting the required dependencies installed so please reference the main tools sites for installation instructions.

Laravel - https://laravel.com/

PHP - PHP can be installed in different ways. If you do not currently have PHP installed, use google to find the best method for you.

MySQL - MySQL can be installed in different ways. If you do not currently have MySQL installed, use google to find the best method for you.

Optional - A visual tool for looking at the database and data is suggested. MySql Workbench is our preference - https://www.mysql.com/products/workbench/

In addition to creating the environment yourself, there are also a lot of different tools that can pull together all the dependencies for you. Homestead is an example.

For windows users, Wampserver64 is a useful tool as it installs the MySql server and php at the same time. https://sourceforge.net/projects/wampserver/

## Database setup (TBD)

-   Create the following schemas in your MySql database. `heroesprofile`, `heroesprofile_cache`, `heroesprofile_hi`, `heroesprofile_hi_nc`, `heroesprofile_mcl`, `heroesprofile_ml`, `heroesprofile_ngs`, `heroesprofile_nutcup`
-   Table structure and table data will be provided on an as-needed basis.  If you are wanting to make a change and need to know this information, please log a ticket or contact us directly explaining what you are doing and what data you need.

## Project Setup

-   From the command line, navigate to the heroesprofile repository.
-   Configure `.env` file using `.env.example`
-   Run `npm install`
-   Run `composer install`
-   Run `php artisan key:generate` make sure the APP_KEY has this value in the .env file

## Running the project

-   From the command line, navigate to the heroesprofile repository.
-   Run `php artisan serve` - spins up the webserver
-   The path to paste into the browser will show up in the command line.
-   From a second command line, navigate to the heroesprofile repository.
-   Run `npm run dev` - - watches for any changes and automatically recompiles


# Contributing

All contributions are welcome. The owners of Heroes Profile reserve the right to include or deny any merge requests from the community. Also, please try and only create pull requests that contain updates to the specific update you want to make. Including environment or auto-generated updates to framework code that are not required for your change only complicates making updates.

If a contribution requires changes to the database, or how the data is grabbed from replays, please log an issue report detailing your need.
