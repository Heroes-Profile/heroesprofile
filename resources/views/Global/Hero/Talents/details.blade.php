<?php
namespace App;
use Session;


$global_data_details = \GlobalHeroTalentDataDetails::instance(1, array('2.49.2.77981'), array('5'), array(),
                                      array(), array(), array(), array(), array(0), array());
$return_data = $global_data_details->getGlobalTalentDetailData();



print_r($return_data->toJson());
