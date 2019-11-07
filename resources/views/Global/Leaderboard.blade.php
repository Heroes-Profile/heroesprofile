<?php
$data = \PlayerData::instance()->getPlayerData();
print_r(json_encode($data,true));
 ?>
