<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

$attachment = new stdClass();
$attachment->network_prefixes = ['192.168.100.10', '192.168.200.10'];
p($api->api_sites_create([
  'id' => 'SiteName_10',
  'name' => 'Site Name 10',
  'attachment' => $attachment,
]));

p($api->_api_list('sites'));