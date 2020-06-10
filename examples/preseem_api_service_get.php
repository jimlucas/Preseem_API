<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->_api_get('services', 'ServiceLocation_4321'));
