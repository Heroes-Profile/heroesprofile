<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class replay_brawl_seeder extends Seeder
{

    /**
     * The database schema.
     *
     * @var DB
     */
    protected $connection;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = DB::connection(config('database.brawl'));
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->connection->table('replay')->insert([
        ['replayID' => '17465764','game_date' => '2019-08-22 09:51:40','game_length' => '993','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465774','game_date' => '2019-08-22 09:51:40','game_length' => '931','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465776','game_date' => '2019-08-22 09:51:40','game_length' => '1122','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465780','game_date' => '2019-08-22 09:51:40','game_length' => '1584','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465781','game_date' => '2019-08-22 09:51:40','game_length' => '919','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465785','game_date' => '2019-08-22 09:51:40','game_length' => '931','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465794','game_date' => '2019-08-22 09:51:40','game_length' => '617','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465804','game_date' => '2019-08-22 09:51:40','game_length' => '1166','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465817','game_date' => '2019-08-22 09:51:40','game_length' => '734','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465819','game_date' => '2019-08-22 09:51:40','game_length' => '1101','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465820','game_date' => '2019-08-22 09:51:40','game_length' => '765','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465822','game_date' => '2019-08-22 09:51:40','game_length' => '532','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465843','game_date' => '2019-08-22 09:51:40','game_length' => '734','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465850','game_date' => '2019-08-22 09:51:40','game_length' => '1008','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465867','game_date' => '2019-08-22 09:51:40','game_length' => '1078','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465873','game_date' => '2019-08-22 09:51:40','game_length' => '785','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465893','game_date' => '2019-08-22 09:51:40','game_length' => '1411','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465894','game_date' => '2019-08-22 09:51:40','game_length' => '1009','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465896','game_date' => '2019-08-22 09:51:40','game_length' => '597','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465903','game_date' => '2019-08-22 09:51:40','game_length' => '732','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465905','game_date' => '2019-08-22 09:51:40','game_length' => '862','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465909','game_date' => '2019-08-22 09:51:40','game_length' => '1277','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465930','game_date' => '2019-08-22 09:51:40','game_length' => '1516','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465958','game_date' => '2019-08-22 09:51:40','game_length' => '776','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465962','game_date' => '2019-08-22 09:51:40','game_length' => '1232','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465966','game_date' => '2019-08-22 09:51:40','game_length' => '897','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465969','game_date' => '2019-08-22 09:51:40','game_length' => '1451','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465970','game_date' => '2019-08-22 09:51:40','game_length' => '779','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465979','game_date' => '2019-08-22 09:51:40','game_length' => '1521','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465983','game_date' => '2019-08-22 09:51:40','game_length' => '917','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17465988','game_date' => '2019-08-22 09:51:40','game_length' => '1006','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466012','game_date' => '2019-08-22 09:51:40','game_length' => '1011','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466020','game_date' => '2019-08-22 09:51:40','game_length' => '1397','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466037','game_date' => '2019-08-22 09:51:40','game_length' => '775','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466047','game_date' => '2019-08-22 09:51:40','game_length' => '1102','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466060','game_date' => '2019-08-22 09:51:40','game_length' => '892','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466080','game_date' => '2019-08-22 09:51:40','game_length' => '1409','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466145','game_date' => '2019-08-22 09:51:40','game_length' => '2071','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466314','game_date' => '2019-08-22 09:51:40','game_length' => '2057','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466346','game_date' => '2019-08-22 09:51:40','game_length' => '1143','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466366','game_date' => '2019-08-22 09:51:40','game_length' => '801','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466371','game_date' => '2019-08-22 09:51:40','game_length' => '1201','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466372','game_date' => '2019-08-22 09:51:40','game_length' => '879','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466373','game_date' => '2019-08-22 09:51:40','game_length' => '988','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466374','game_date' => '2019-08-22 09:51:40','game_length' => '1027','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466375','game_date' => '2019-08-22 09:51:40','game_length' => '1158','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466376','game_date' => '2019-08-22 09:51:40','game_length' => '1096','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466377','game_date' => '2019-08-22 09:51:40','game_length' => '830','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466378','game_date' => '2019-08-22 09:51:40','game_length' => '1064','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466379','game_date' => '2019-08-22 09:51:40','game_length' => '1460','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466380','game_date' => '2019-08-22 09:51:40','game_length' => '1738','game_map' => '20','game_version' => '2.46.1.75132','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466405','game_date' => '2019-08-22 09:51:40','game_length' => '1421','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466408','game_date' => '2019-08-22 09:51:40','game_length' => '1272','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466411','game_date' => '2019-08-22 09:51:40','game_length' => '740','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466433','game_date' => '2019-08-22 09:51:40','game_length' => '1194','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466477','game_date' => '2019-08-22 09:51:40','game_length' => '964','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466481','game_date' => '2019-08-22 09:51:40','game_length' => '705','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466483','game_date' => '2019-08-22 09:51:40','game_length' => '915','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466713','game_date' => '2019-08-22 09:51:40','game_length' => '639','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466728','game_date' => '2019-08-22 09:51:40','game_length' => '1384','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466747','game_date' => '2019-08-22 09:51:40','game_length' => '527','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466762','game_date' => '2019-08-22 09:51:40','game_length' => '1112','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466783','game_date' => '2019-08-22 09:51:40','game_length' => '514','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466793','game_date' => '2019-08-22 09:51:40','game_length' => '795','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466819','game_date' => '2019-08-22 09:51:40','game_length' => '768','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466824','game_date' => '2019-08-22 09:51:40','game_length' => '1027','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466923','game_date' => '2019-08-22 09:51:40','game_length' => '862','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466934','game_date' => '2019-08-22 09:51:40','game_length' => '1055','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466935','game_date' => '2019-08-22 09:51:40','game_length' => '946','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17466957','game_date' => '2019-08-22 09:51:40','game_length' => '984','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467230','game_date' => '2019-08-22 09:51:40','game_length' => '835','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467233','game_date' => '2019-08-22 09:51:40','game_length' => '944','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467238','game_date' => '2019-08-22 09:51:40','game_length' => '892','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467256','game_date' => '2019-08-22 09:51:40','game_length' => '979','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467257','game_date' => '2019-08-22 09:51:40','game_length' => '875','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467307','game_date' => '2019-08-22 09:51:40','game_length' => '923','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467331','game_date' => '2019-08-22 09:51:40','game_length' => '912','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467343','game_date' => '2019-08-22 09:51:40','game_length' => '1139','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467419','game_date' => '2019-08-22 09:51:40','game_length' => '884','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467440','game_date' => '2019-08-22 09:51:40','game_length' => '1100','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467444','game_date' => '2019-08-22 09:51:40','game_length' => '1020','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467451','game_date' => '2019-08-22 09:51:40','game_length' => '1066','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467462','game_date' => '2019-08-22 09:51:40','game_length' => '782','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467467','game_date' => '2019-08-22 09:51:40','game_length' => '834','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467492','game_date' => '2019-08-22 09:51:40','game_length' => '1211','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467558','game_date' => '2019-08-22 09:51:40','game_length' => '1338','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467559','game_date' => '2019-08-22 09:51:40','game_length' => '1408','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467612','game_date' => '2019-08-22 09:51:40','game_length' => '972','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467613','game_date' => '2019-08-22 09:51:40','game_length' => '985','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467633','game_date' => '2019-08-22 09:51:40','game_length' => '883','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467663','game_date' => '2019-08-22 09:51:40','game_length' => '2115','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467668','game_date' => '2019-08-22 09:51:40','game_length' => '929','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17467954','game_date' => '2019-08-22 09:51:40','game_length' => '743','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468029','game_date' => '2019-08-22 09:51:40','game_length' => '852','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468039','game_date' => '2019-08-22 09:51:40','game_length' => '883','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468070','game_date' => '2019-08-22 09:51:40','game_length' => '865','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468080','game_date' => '2019-08-22 09:51:40','game_length' => '1048','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468141','game_date' => '2019-08-22 09:51:40','game_length' => '940','game_map' => '20','game_version' => '2.47.0.75589','region' => '5','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468151','game_date' => '2019-08-22 09:51:40','game_length' => '1056','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1'],
        ['replayID' => '17468201','game_date' => '2019-08-22 09:51:40','game_length' => '850','game_map' => '20','game_version' => '2.47.0.75589','region' => '1','date_added' => '2019-08-22 09:51:40', 'globals_ran' => '1']
      ]);
    }
}
