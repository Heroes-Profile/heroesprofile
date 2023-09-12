# Heroes Profile

[![](https://github.com/Heroes-Profile/heroesprofile/workflows/PHPUnit/badge.svg)](https://github.com/Heroes-Profile/heroesprofile/actions?query=workflow%3A%22PHPUnit%22)
[![](https://github.com/Heroes-Profile/heroesprofile/workflows/PHPStan/badge.svg)](https://github.com/Heroes-Profile/heroesprofile/actions?query=workflow%3A%PHPStan%22)

[Master Heroes Profile](https://alpha.heroesprofile.com/)(master branch) or [Develop Heroes Profile](https://heroesprofile-dev.ue.r.appspot.com//)(develop branch) is an open Heroes of the Storm stat site. Providing players with Global Hero Statistics, Personal Profile, MMR, Comparisons, Amateur series, and much more.

This public repository is the site re-write for [Heroes Profile](https://www.heroesprofile.com/) and is not currently in production.

# Cloning the Heroes Profile repository

-   `git clone --recursive https://github.com/Heroes-Profile/heroesprofile.git`

## Installation

Heroes Profile is a PHP/Laravel bootstrap app. Making use of a MySql database. Every system has different methods for getting the required dependencies installed so please reference the main tools sites for installation instructions.

Laravel - https://laravel.com/

PHP - PHP can be installed in different ways. If you do not currently have PHP installed, use google to find the best method for you.

-   Increase your local PHP memory_limit var. We use 1g. memory_limit = 1G

MySQL - MySQL can be installed in different ways. If you do not currently have MySQL installed, use google to find the best method for you.

Optional - A visual tool for looking at the database and data is suggested. MySql Workbench is our preference - https://www.mysql.com/products/workbench/

In addition to creating the environment yourself, there are also a lot of different tools that can pull together all the dependencies for you. Homestead is an example.

For windows users, Wampserver64 is a useful tool as it installs the MySql server and php at the same time. https://sourceforge.net/projects/wampserver/

## Database setup

-   Create the following schemas in your MySql database. `heroesprofile`, `heroesprofile_cache`
-   Increase your local mysql max_allowed_packet var. We use 64M.

## Project Setup

-   From the command line, navigate to the heroesprofile repository.
-   Configure `.env` file using `.env.example`
-   Run `composer install`
-   Run `sh images.sh` to add the latest image files from game data<sup>1</sup>
-   Run `php artisan key:generate` make sure the APP_KEY has this value in the .env file
-   Run `npm install`
-   Run `npm run dev`
-   Run `php artisan migrate`
-   Run `composer dump-autoload`
-   Run `php artisan db:seed`

> <sup>1</sup> The image injection script is written for *nix systems. On Windows you will need to clone/download the latest version of
> https://github.com/HeroesToolChest/heroes-images and copy the **heroesimages** folder into **public/images**.

## Running the project

-   From the command line, navigate to the heroesprofile repository.
-   Run `php artisan serve` - spins up the webserver
-   The path to paste into the browser will show up in the command line.
-   From a second command line, navigate to the heroesprofile repository.
-   Run `npm run watch` - watches for any changes and automatically recompiles

# Contributing

All contributions are welcome. The owners of Heroes Profile reserve the right to include or deny any merge requests from the community. Also, please try and only create pull requests that contain updates to the specific update you want to make. Including environment or auto-generated updates to framework code that are not required for your change only complicates making updates.

If a contribution requires changes to the database, or how the data is grabbed from replays, please log an issue report detailing your need.

-   If backend code from the production site is needed, it can be found in the live_side_code folder of this repository. If there is code you need that is missing, please create a PR to have that added.
