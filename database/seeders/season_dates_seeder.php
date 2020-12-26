<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class season_dates_seeder extends Seeder
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
      $this->connection->table('season_dates')->insert([
        ['id' => '1','year' => '2016','season' => '1','start_date' => '2014-06-14 12:01:00','end_date' => '2016-09-13 12:00:00'],['id' => '2','year' => '2016','season' => '2','start_date' => '2016-09-13 12:01:00','end_date' => '2016-12-13 12:00:00'],['id' => '3','year' => '2016','season' => '3','start_date' => '2016-12-13 12:01:00','end_date' => '2017-03-14 12:00:00'],['id' => '4','year' => '2017','season' => '1','start_date' => '2017-03-14 12:01:00','end_date' => '2017-06-13 12:00:00'],['id' => '5','year' => '2017','season' => '2','start_date' => '2017-06-13 12:01:00','end_date' => '2017-09-05 12:00:00'],['id' => '6','year' => '2017','season' => '3','start_date' => '2017-09-05 12:01:00','end_date' => '2017-12-12 12:00:00'],['id' => '7','year' => '2018','season' => '1','start_date' => '2017-12-12 12:01:00','end_date' => '2018-03-06 12:00:00'],['id' => '8','year' => '2018','season' => '2','start_date' => '2018-03-06 12:01:00','end_date' => '2018-07-10 12:00:00'],['id' => '9','year' => '2018','season' => '3','start_date' => '2018-07-10 12:01:00','end_date' => '2018-09-25 12:00:00'],['id' => '10','year' => '2018','season' => '4','start_date' => '2018-09-25 12:01:00','end_date' => '2018-12-11 12:00:00'],['id' => '11','year' => '2019','season' => '1','start_date' => '2018-12-11 12:01:00','end_date' => '2019-03-26 12:00:00'],['id' => '12','year' => '2019','season' => '1.5','start_date' => '2019-03-26 12:01:00','end_date' => '2019-08-06 12:00:00'],['id' => '13','year' => '2019','season' => '2','start_date' => '2019-08-06 12:01:00','end_date' => '2019-12-03 12:00:00'],['id' => '14','year' => '2020','season' => '1','start_date' => '2019-12-03 12:01:00','end_date' => '2020-04-14 12:00:00'],['id' => '15','year' => '2020','season' => '2','start_date' => '2020-04-14 12:01:00','end_date' => '2020-12-14 12:00:00']
      ]);

    }
}
