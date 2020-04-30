<?php

use Illuminate\Database\Seeder;

class master_games_played_data_seeder extends Seeder
{

    /**
     * The database schema.
     *
     * @var DB
     */
    protected $connection;

    /**
     * Create a new seed instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = DB::connection(config('database.default'));
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/master_games_played_data.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
