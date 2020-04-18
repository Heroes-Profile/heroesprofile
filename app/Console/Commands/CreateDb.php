<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates databases';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // create heroesprofile databases

        if(!$this->get('heroesprofile')) {
            DB::statement('CREATE DATABASE heroesprofile');
        }
        if(!$this->get('heroesprofile_brawl')) {
            DB::statement('CREATE DATABASE heroesprofile_brawl');
        }
        if(!$this->get('heroesprofile_cache')) {
            DB::statement('CREATE DATABASE heroesprofile_cache');
        }

        if(!$this->get('heroesprofile_optout')) {
            DB::statement('CREATE DATABASE heroesprofile_optout');
        }
    }

    /**
     * gets a database
     */
    public function get($your_database_name)
    {
         $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
         $db = DB::select($query, [$your_database_name]);
         if (empty($db)) {
             echo "$your_database_name DOES NOT exist\n";
             return false;
         } else {
            echo "$your_database_name ALREADY exists\n";
             return true;
         }
    }
}
