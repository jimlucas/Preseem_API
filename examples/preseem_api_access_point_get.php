<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->_api_get('access_points', '10'));
