<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

$attachment = new stdClass();
$attachment->cpe_mac = '00:10:0b:6e:4c:ff';
$attachment->network_prefixes = array('12.12.12.12');
p($api->api_services_create([
  'id' => 'ServiceLocation_4321',
  'account' => 'CustomerName_1234',
  'package' => '',
  'parent_device_id' => '',
  'up_speed' => 2000,
  'down_speed' => 10000,
  'attachments' => array($attachment),
]));

p($api->_api_list('services'));

