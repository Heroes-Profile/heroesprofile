<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        ['table_cache_value_id' =>  '8','table_to_cache' =>  'leaderboard','season' =>  '13','cache_number' =>  '1','date_cached' =>  '2020-02-07 08:44:06'],
        ['table_cache_value_id' =>  '9','table_to_cache' =>  'leaderboard','season' =>  '13','cache_number' =>  '2','date_cached' =>  '2020-02-07 08:58:55'],
        ['table_cache_value_id' =>  '10','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '3','date_cached' =>  '2020-02-07 09:06:11'],
        ['table_cache_value_id' =>  '11','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '4','date_cached' =>  '2020-02-08 02:03:43'],
        ['table_cache_value_id' =>  '12','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '5','date_cached' =>  '2020-02-09 02:03:22'],
        ['table_cache_value_id' =>  '13','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '6','date_cached' =>  '2020-02-10 02:03:40'],
        ['table_cache_value_id' =>  '14','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '7','date_cached' =>  '2020-02-11 02:04:19'],
        ['table_cache_value_id' =>  '15','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '8','date_cached' =>  '2020-02-11 02:04:19'],
        ['table_cache_value_id' =>  '16','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '9','date_cached' =>  '2020-02-24 02:04:19'],
        ['table_cache_value_id' =>  '17','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '10','date_cached' =>  '2020-02-24 08:41:37'],
        ['table_cache_value_id' =>  '18','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '11','date_cached' =>  '2020-02-25 02:03:36'],
        ['table_cache_value_id' =>  '19','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '12','date_cached' =>  '2020-02-26 02:03:33'],
        ['table_cache_value_id' =>  '20','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '13','date_cached' =>  '2020-02-27 02:03:52'],
        ['table_cache_value_id' =>  '21','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '14','date_cached' =>  '2020-02-28 02:05:49'],
        ['table_cache_value_id' =>  '22','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '15','date_cached' =>  '2020-02-29 02:04:52'],
        ['table_cache_value_id' =>  '23','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '16','date_cached' =>  '2020-03-01 02:04:13'],
        ['table_cache_value_id' =>  '24','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '17','date_cached' =>  '2020-03-02 02:06:21'],
        ['table_cache_value_id' =>  '25','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '18','date_cached' =>  '2020-03-03 02:05:20'],
        ['table_cache_value_id' =>  '26','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '19','date_cached' =>  '2020-03-04 02:03:18'],
        ['table_cache_value_id' =>  '27','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '20','date_cached' =>  '2020-03-05 02:03:36'],
        ['table_cache_value_id' =>  '28','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '21','date_cached' =>  '2020-03-05 08:43:11'],
        ['table_cache_value_id' =>  '29','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '22','date_cached' =>  '2020-03-06 02:03:23'],
        ['table_cache_value_id' =>  '30','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '23','date_cached' =>  '2020-03-07 02:03:00'],
        ['table_cache_value_id' =>  '31','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '24','date_cached' =>  '2020-03-08 13:41:52'],
        ['table_cache_value_id' =>  '32','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '25','date_cached' =>  '2020-03-09 02:03:01'],
        ['table_cache_value_id' =>  '33','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '26','date_cached' =>  '2020-03-10 02:03:19'],
        ['table_cache_value_id' =>  '34','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '27','date_cached' =>  '2020-03-11 02:03:37'],
        ['table_cache_value_id' =>  '35','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '28','date_cached' =>  '2020-03-12 02:03:19'],
        ['table_cache_value_id' =>  '36','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '29','date_cached' =>  '2020-03-13 02:03:25'],
        ['table_cache_value_id' =>  '37','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '30','date_cached' =>  '2020-03-14 02:03:07'],
        ['table_cache_value_id' =>  '38','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '31','date_cached' =>  '2020-03-15 02:02:58'],
        ['table_cache_value_id' =>  '39','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '32','date_cached' =>  '2020-03-16 02:03:11'],
        ['table_cache_value_id' =>  '40','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '33','date_cached' =>  '2020-03-16 08:46:33'],
        ['table_cache_value_id' =>  '41','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '34','date_cached' =>  '2020-03-17 02:04:06'],
        ['table_cache_value_id' =>  '42','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '35','date_cached' =>  '2020-03-18 02:05:14'],
        ['table_cache_value_id' =>  '43','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '36','date_cached' =>  '2020-03-19 02:05:12'],
        ['table_cache_value_id' =>  '44','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '37','date_cached' =>  '2020-03-20 02:03:41'],
        ['table_cache_value_id' =>  '45','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '38','date_cached' =>  '2020-03-21 02:05:12'],
        ['table_cache_value_id' =>  '46','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '39','date_cached' =>  '2020-03-22 02:05:19'],
        ['table_cache_value_id' =>  '47','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '40','date_cached' =>  '2020-03-23 02:05:11'],
        ['table_cache_value_id' =>  '48','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '41','date_cached' =>  '2020-03-24 02:05:18'],
        ['table_cache_value_id' =>  '49','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '42','date_cached' =>  '2020-03-25 02:04:03'],
        ['table_cache_value_id' =>  '50','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '43','date_cached' =>  '2020-03-26 02:05:11'],
        ['table_cache_value_id' =>  '51','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '44','date_cached' =>  '2020-03-27 02:06:00'],
        ['table_cache_value_id' =>  '52','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '45','date_cached' =>  '2020-03-28 02:04:20'],
        ['table_cache_value_id' =>  '53','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '46','date_cached' =>  '2020-03-29 02:03:31'],
        ['table_cache_value_id' =>  '54','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '47','date_cached' =>  '2020-03-31 02:03:59'],
        ['table_cache_value_id' =>  '55','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '48','date_cached' =>  '2020-04-01 02:06:02'],
        ['table_cache_value_id' =>  '56','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '49','date_cached' =>  '2020-04-02 02:07:25'],
        ['table_cache_value_id' =>  '57','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '55','date_cached' =>  '2020-04-11 19:08:00'],
        ['table_cache_value_id' =>  '58','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '56','date_cached' =>  '2020-04-12 11:03:25'],
        ['table_cache_value_id' =>  '59','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '57','date_cached' =>  '2020-04-13 02:52:42'],
        ['table_cache_value_id' =>  '60','table_to_cache' =>  'leaderboard','season' =>  '14','cache_number' =>  '58','date_cached' =>  '2020-04-14 02:47:01'],
        ['table_cache_value_id' =>  '61','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '59','date_cached' =>  '2020-04-15 02:05:17'],
        ['table_cache_value_id' =>  '62','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '60','date_cached' =>  '2020-04-16 02:15:34'],
        ['table_cache_value_id' =>  '63','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '61','date_cached' =>  '2020-04-17 02:15:11'],
        ['table_cache_value_id' =>  '64','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '62','date_cached' =>  '2020-04-18 02:18:57'],
        ['table_cache_value_id' =>  '65','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '63','date_cached' =>  '2020-04-19 02:25:47'],
        ['table_cache_value_id' =>  '66','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '64','date_cached' =>  '2020-04-20 02:30:52'],
        ['table_cache_value_id' =>  '67','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '65','date_cached' =>  '2020-04-21 02:25:51'],
        ['table_cache_value_id' =>  '68','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '66','date_cached' =>  '2020-04-22 02:29:32'],
        ['table_cache_value_id' =>  '69','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '67','date_cached' =>  '2020-04-23 02:33:27'],
        ['table_cache_value_id' =>  '70','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '68','date_cached' =>  '2020-04-24 02:32:44'],
        ['table_cache_value_id' =>  '71','table_to_cache' =>  'leaderboard','season' =>  '15','cache_number' =>  '69','date_cached' =>  '2020-04-25 02:20:03']
      ]);
    }
}
