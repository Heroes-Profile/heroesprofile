<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class mmr_type_ids_seeder extends Seeder
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
      $this->connection->table('mmr_type_ids')->insert([
        ["mmr_type_id" => "1","name" => "Abathur"],["mmr_type_id" => "2","name" => "Alarak"],["mmr_type_id" => "3","name" => "Anub'arak"],["mmr_type_id" => "4","name" => "Artanis"],["mmr_type_id" => "5","name" => "Arthas"],["mmr_type_id" => "6","name" => "Auriel"],["mmr_type_id" => "7","name" => "Azmodan"],["mmr_type_id" => "8","name" => "Brightwing"],["mmr_type_id" => "9","name" => "Cassia"],["mmr_type_id" => "10","name" => "Chen"],["mmr_type_id" => "11","name" => "Cho"],["mmr_type_id" => "12","name" => "Chromie"],["mmr_type_id" => "13","name" => "D.Va"],["mmr_type_id" => "14","name" => "Dehaka"],["mmr_type_id" => "15","name" => "Diablo"],["mmr_type_id" => "16","name" => "E.T.C."],["mmr_type_id" => "17","name" => "Falstad"],["mmr_type_id" => "18","name" => "Gall"],["mmr_type_id" => "19","name" => "Garrosh"],["mmr_type_id" => "20","name" => "Gazlowe"],["mmr_type_id" => "21","name" => "Genji"],["mmr_type_id" => "22","name" => "Greymane"],["mmr_type_id" => "23","name" => "Gul'dan"],["mmr_type_id" => "24","name" => "Illidan"],["mmr_type_id" => "25","name" => "Jaina"],["mmr_type_id" => "26","name" => "Johanna"],["mmr_type_id" => "27","name" => "Kael'thas"],["mmr_type_id" => "28","name" => "Kerrigan"],["mmr_type_id" => "29","name" => "Kharazim"],["mmr_type_id" => "30","name" => "Leoric"],["mmr_type_id" => "31","name" => "Li Li"],["mmr_type_id" => "32","name" => "Li-Ming"],["mmr_type_id" => "33","name" => "Lt. Morales"],["mmr_type_id" => "34","name" => "Lúcio"],["mmr_type_id" => "35","name" => "Lunara"],["mmr_type_id" => "36","name" => "Malfurion"],["mmr_type_id" => "37","name" => "Malthael"],["mmr_type_id" => "38","name" => "Medivh"],["mmr_type_id" => "39","name" => "Muradin"],["mmr_type_id" => "40","name" => "Murky"],["mmr_type_id" => "41","name" => "Nazeebo"],["mmr_type_id" => "42","name" => "Nova"],["mmr_type_id" => "43","name" => "Probius"],["mmr_type_id" => "44","name" => "Ragnaros"],["mmr_type_id" => "45","name" => "Raynor"],["mmr_type_id" => "46","name" => "Rehgar"],["mmr_type_id" => "47","name" => "Rexxar"],["mmr_type_id" => "48","name" => "Samuro"],["mmr_type_id" => "49","name" => "Sgt. Hammer"],["mmr_type_id" => "50","name" => "Sonya"],["mmr_type_id" => "51","name" => "Stitches"],["mmr_type_id" => "52","name" => "Stukov"],["mmr_type_id" => "53","name" => "Sylvanas"],["mmr_type_id" => "54","name" => "Tassadar"],["mmr_type_id" => "55","name" => "The Butcher"],["mmr_type_id" => "56","name" => "The Lost Vikings"],["mmr_type_id" => "57","name" => "Thrall"],["mmr_type_id" => "58","name" => "Tracer"],["mmr_type_id" => "59","name" => "Tychus"],["mmr_type_id" => "60","name" => "Tyrael"],["mmr_type_id" => "61","name" => "Tyrande"],["mmr_type_id" => "62","name" => "Uther"],["mmr_type_id" => "63","name" => "Valeera"],["mmr_type_id" => "64","name" => "Valla"],["mmr_type_id" => "65","name" => "Varian"],["mmr_type_id" => "66","name" => "Xul"],["mmr_type_id" => "67","name" => "Zagara"],["mmr_type_id" => "68","name" => "Zarya"],["mmr_type_id" => "69","name" => "Zeratul"],["mmr_type_id" => "70","name" => "Zul'jin"],["mmr_type_id" => "71","name" => "Kel'Thuzad"],["mmr_type_id" => "72","name" => "Ana"],["mmr_type_id" => "73","name" => "Junkrat"],["mmr_type_id" => "74","name" => "Alexstrasza"],["mmr_type_id" => "75","name" => "Hanzo"],["mmr_type_id" => "77","name" => "Blaze"],["mmr_type_id" => "78","name" => "Maiev"],["mmr_type_id" => "79","name" => "Fenix"],["mmr_type_id" => "80","name" => "Deckard"],["mmr_type_id" => "81","name" => "Yrel"],["mmr_type_id" => "82","name" => "Whitemane"],["mmr_type_id" => "83","name" => "Mephisto"],["mmr_type_id" => "84","name" => "Mal'Ganis"],["mmr_type_id" => "85","name" => "Orphea"],["mmr_type_id" => "86","name" => "Imperius"],["mmr_type_id" => "87","name" => "Anduin"],["mmr_type_id" => "88","name" => "Qhira"],["mmr_type_id" => "89","name" => "Deathwing"],["mmr_type_id" => "10000","name" => "player"],["mmr_type_id" => "100000","name" => "Support"],["mmr_type_id" => "100001","name" => "Melee Assassin"],["mmr_type_id" => "100002","name" => "Tank"],["mmr_type_id" => "100003","name" => "Bruiser"],["mmr_type_id" => "100004","name" => "Healer"],["mmr_type_id" => "100005","name" => "Ranged Assassin"]
      ]);

    }
}
