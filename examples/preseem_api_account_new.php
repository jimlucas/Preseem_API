<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->api_accounts_create([
  'id' => 'CustomerName_1234',
  'name' => 'Customer Name',
]));

p($api->_api_list('accounts'));
