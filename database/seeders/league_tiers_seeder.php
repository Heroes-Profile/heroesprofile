<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class league_tiers_seeder extends Seeder
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
      $this->connection->table('league_tiers')->insert([
        ['tier_id' => '1','name' => 'bronze'],['tier_id' => '2','name' => 'silver'],['tier_id' => '3','name' => 'gold'],['tier_id' => '4','name' => 'platinum'],['tier_id' => '5','name' => 'diamond'],['tier_id' => '6','name' => 'master'],['tier_id' => '7','name' => 'all']
      ]);

    }
}
