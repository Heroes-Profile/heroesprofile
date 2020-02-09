<?php
$global_data = \GlobalHeroStatsData::instance(array('2.49.2.77981'), array('5'), array(),
                                      array(), array(), array(), array(), array(0), array());
$data = $global_data->getGlobalHeroStatData();
print_r($data->toJson());
?>
