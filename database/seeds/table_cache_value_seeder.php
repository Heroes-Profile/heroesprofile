<?php

use Illuminate\Database\Seeder;

class table_cache_value_seeder extends Seeder
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
        $this->connection = DB::connection(config('database.cache'));
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->connection->table('table_cache_value')->insert([
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '1','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '2','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '3','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '4','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '5','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '6','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '7','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '8','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '9','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '10','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '11','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '12','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '13','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '14','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '15','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '16','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '17','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '18','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '19','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '20','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '21','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '22','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '23','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '24','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '25','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '26','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '27','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '28','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '29','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '30','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '31','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '32','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '33','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '34','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '35','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '36','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '37','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '38','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '39','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '40','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '41','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '42','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '43','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '44','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '45','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '46','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '47','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '48','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '49','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '50','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '51','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '52','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '53','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '54','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '55','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '56','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '57','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '58','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '59','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '60','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '61','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '62','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '63','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '64','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '65','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '66','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '67','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '68','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '69','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '70','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '71','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '72','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '73','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '74','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '75','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '76','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '77','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '78','date_cached' => '2019-10-02 16:54:21'],
        ['table_to_cache' => 'leaderboard','season' => '14', 'cache_number' => '79','date_cached' => '2019-10-02 16:54:21']
      ]);
    }
}
