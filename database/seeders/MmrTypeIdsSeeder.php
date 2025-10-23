<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MmrTypeIdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['100006', '1', 'Abathur'],
            ['100007', '2', 'Alarak'],
            ['100008', '3', 'Anub\'arak'],
            ['100009', '4', 'Artanis'],
            ['100010', '5', 'Arthas'],
            ['100011', '6', 'Auriel'],
            ['100012', '7', 'Azmodan'],
            ['100013', '8', 'Brightwing'],
            ['100014', '9', 'Cassia'],
            ['100015', '10', 'Chen'],
            ['100016', '11', 'Cho'],
            ['100017', '12', 'Chromie'],
            ['100018', '13', 'D.Va'],
            ['100019', '14', 'Dehaka'],
            ['100020', '15', 'Diablo'],
            ['100021', '16', 'E.T.C.'],
            ['100022', '17', 'Falstad'],
            ['100023', '18', 'Gall'],
            ['100024', '19', 'Garrosh'],
            ['100025', '20', 'Gazlowe'],
            ['100026', '21', 'Genji'],
            ['100027', '22', 'Greymane'],
            ['100028', '23', 'Gul\'dan'],
            ['100029', '24', 'Illidan'],
            ['100030', '25', 'Jaina'],
            ['100031', '26', 'Johanna'],
            ['100032', '27', 'Kael\'thas'],
            ['100033', '28', 'Kerrigan'],
            ['100034', '29', 'Kharazim'],
            ['100035', '30', 'Leoric'],
            ['100036', '31', 'Li Li'],
            ['100037', '32', 'Li-Ming'],
            ['100038', '33', 'Lt. Morales'],
            ['100039', '34', 'Lúcio'],
            ['100040', '35', 'Lunara'],
            ['100041', '36', 'Malfurion'],
            ['100042', '37', 'Malthael'],
            ['100043', '38', 'Medivh'],
            ['100044', '39', 'Muradin'],
            ['100045', '40', 'Murky'],
            ['100046', '41', 'Nazeebo'],
            ['100047', '42', 'Nova'],
            ['100048', '43', 'Probius'],
            ['100049', '44', 'Ragnaros'],
            ['100050', '45', 'Raynor'],
            ['100051', '46', 'Rehgar'],
            ['100052', '47', 'Rexxar'],
            ['100053', '48', 'Samuro'],
            ['100054', '49', 'Sgt. Hammer'],
            ['100055', '50', 'Sonya'],
            ['100056', '51', 'Stitches'],
            ['100057', '52', 'Stukov'],
            ['100058', '53', 'Sylvanas'],
            ['100059', '54', 'Tassadar'],
            ['100060', '55', 'The Butcher'],
            ['100061', '56', 'The Lost Vikings'],
            ['100062', '57', 'Thrall'],
            ['100063', '58', 'Tracer'],
            ['100064', '59', 'Tychus'],
            ['100065', '60', 'Tyrael'],
            ['100066', '61', 'Tyrande'],
            ['100067', '62', 'Uther'],
            ['100068', '63', 'Valeera'],
            ['100069', '64', 'Valla'],
            ['100070', '65', 'Varian'],
            ['100071', '66', 'Xul'],
            ['100072', '67', 'Zagara'],
            ['100073', '68', 'Zarya'],
            ['100074', '69', 'Zeratul'],
            ['100075', '70', 'Zul\'jin'],
            ['100076', '71', 'Kel\'Thuzad'],
            ['100077', '72', 'Ana'],
            ['100078', '73', 'Junkrat'],
            ['100079', '74', 'Alexstrasza'],
            ['100080', '75', 'Hanzo'],
            ['100081', '77', 'Blaze'],
            ['100082', '78', 'Maiev'],
            ['100083', '79', 'Fenix'],
            ['100084', '80', 'Deckard'],
            ['100085', '81', 'Yrel'],
            ['100086', '82', 'Whitemane'],
            ['100087', '83', 'Mephisto'],
            ['100088', '84', 'Mal\'Ganis'],
            ['100089', '85', 'Orphea'],
            ['100090', '86', 'Imperius'],
            ['100091', '87', 'Anduin'],
            ['100092', '88', 'Qhira'],
            ['100093', '89', 'Deathwing'],
            ['100094', '90', 'Mei'],
            ['100095', '10000', 'player'],
            ['100096', '100000', 'Support'],
            ['100097', '100001', 'Melee Assassin'],
            ['100098', '100002', 'Tank'],
            ['100099', '100003', 'Bruiser'],
            ['100100', '100004', 'Healer'],
            ['100101', '100005', 'Ranged Assassin'],
            ['100102', '91', 'Hogger'],

        ];

        foreach ($data as $row) {
            DB::table('mmr_type_ids')->insert([
                'mmr_type_id' => $row[1],
                'name' => $row[2],
            ]);
        }
    }
}
