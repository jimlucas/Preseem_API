<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->_api_delete('access_points', '10'));

p($api->_api_list('access_points'));
