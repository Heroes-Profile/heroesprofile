<?php
namespace App;
use Session;


$global_data = \GlobalHeroTalentData::instance(1, array('2.49.2.77981'), array('5'), array(),
                                      array(), array(), array(), array(), array(0), array());
echo "Popular";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('Popular');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "HP";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('HP');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "1";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('1');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "4";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('4');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "7";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('7');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "10";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('10');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "13";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('13');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "16";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('16');
print_r(json_encode($data, true));

echo "<br>";
echo "<br>";
echo "20";
echo "<br>";
$data = $global_data->getGlobalHeroTalentData('20');
print_r(json_encode($data, true));
