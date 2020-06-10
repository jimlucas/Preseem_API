<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->_api_delete('accounts', 'CustomerName_1234'));

p($api->_api_list('accounts'));
