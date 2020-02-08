# Heroes Profile

 [Heroes Profile](https://alpha.heroesprofile.com/) is an open Heroes of the Storm stat site.  Providing players with Global Hero Statistics, Personal Profile, MMR, Comparisons, Amateur series, and much more.

 This public repository is the site re-write for [Heroes Profile](https://www.heroesprofile.com/) and is not currently in production.

# Cloning the Heroes Profile repository
 * `git clone --recursive https://github.com/Heroes-Profile/heroesprofile.git`
 * `cd heroesprofile`
 * `git submodule update --remote`
 * If you have issues getting the seed files to populate in `database/seeds/heroesprofile-seeds/seed-files`, you can pull them directly from https://github.com/Heroes-Profile/heroesprofile-seeds.git

# Docker Setup

Make sure you have docker and docker compose installed. This method will setup separate containers for the `database` and the `app`

 * `docker-compose up -d`
 * wait for
   * database initialisation
   * data migration and seeding
   * dependencies (composer + npm) installation
 * `docker-compose logs -f` to check if there are any issues in setup
 * go to http://localhost

If you need to get into a command promt for the app
 * `docker-compose run app bash` -- will start a command prompt in a new container
 * `docker-compose exec app bash` -- will attach to an existing container if already running

# Manual Setup

 ## Installation

 Heroes Profile is a PHP/Laravel and vue.js app. Making use of a MySql database.  Every system has different methods for getting the required dependencies installed so please reference the main tools sites for installation instructions.

 Laravel - https://laravel.com/

 Vue.js - https://vuejs.org/

 PHP - PHP can be installed in different ways.  If you do not currently have PHP installed, use google to find the best method for you.
 * Increase your local PHP memory_limit var.  We use 1g.  memory_limit = 1G

 MySQL - MySQL can be installed in different ways.  If you do not currently have MySQL installed, use google to find the best method for you.

 Optional - A visual tool for looking at the database and data is suggested.  MySql Workbench is our preference - https://www.mysql.com/products/workbench/


 In addition to creating the environment yourself, there are also a lot of different tools that can pull together all the dependencies for you.  Homestead is an example.

 For windows users, Wampserver64 is a useful tool as it installs the MySql server and php at the same time. https://sourceforge.net/projects/wampserver/

 ## Database setup
 * Create the following schemas in your MySql database.   `heroesprofile`, `heroesprofile_brawl`, `heroesprofile_cache`, `heroesprofile_optout`
 * Increase your local mysql max_allowed_packet var.  We use 64M.

 ## Project Setup
 * From the command line, navigate to the heroesprofile repository.
 * Configure `.env` file using `.env.example`
 * Run `composer install`
 * Run `php artisan key:generate` make sure the APP_KEY has this value in the .env file
 * Run `npm install`
 * Run `php artisan migrate`
 * Run `composer dump-autoload`
 * Run `php artisan db:seed`

 ## Running the project
 * From the command line, navigate to the heroesprofile repository.
 * Run `php artisan serve` - spins up the webserver
 * The path to paste into the browser will show up in the command line.
 * From a second command line, navigate to the heroesprofile repository.
 * Run `npm run watch` - watches for any changes to vue and automatically recompiles

 # Contributing
 All contributions are welcome.  The owners of Heroes Profile reserve the right to include or deny any merge requests from the community.  Also, please try and only create pull requests that contain updates to the specific update you want to make.  Including environment or auto-generated updates to framework code that are not required for your change only complicates making updates.

 If a contribution requires changes to the database, or how the data is grabbed from replays, please log an issue report detailing your need.

 * If backend code from the production site is needed, it can be found in the live_side_code folder of this repository.  If there is code you need that is missing, please create a PR to have that added.
