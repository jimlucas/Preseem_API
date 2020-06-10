<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

p($api->api_sites_delete('SiteName_10'));

p($api->_api_list('sites'));
