<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->_api_delete('packages', 'Gold Package'));

p($api->_api_list('packages'));
