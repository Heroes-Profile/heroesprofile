<?php

use Illuminate\Database\Seeder;

class season_game_versions_seeder extends Seeder
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
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/season_game_versions.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
