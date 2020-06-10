<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->api_access_points_create([
  'id' => '10',
  'name' => 'Access Point #10',
  'tower' => 'Cline Butte ',
  'ip_address' => '192.168.10.10',
]));

p($api->_api_get('access_points', '10'));

