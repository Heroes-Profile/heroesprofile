<?php
namespace App;
use Session;


$global_data = \GlobalHeroTalentData::instance(1, array('2.49.2.77981'), array('5'), array(),
                                      array(), array(), array(), array(), array(0), array());
$data = $global_data->getGlobalHeroTalentData();
//print_r($data->toJson());
