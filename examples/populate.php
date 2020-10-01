<?php

include_once __DIR__ . '/../init.php';

$api = new Preseem();

if ( false )
{
  foreach (array('access_points', 'accounts', 'packages', 'services', 'sites') AS $object)
  {
    $results = $api->_api_list($object);
    foreach ($results->data AS $item)
    {
      $api->_api_delete($object, $item->id);
    }
    p($api->_api_list($object));
  }
}

if ( false )
{
  foreach ( range(41, 240) AS $id )
  {
    $api->api_accounts_create([
      'id' => "{$id}",
      'name' => "Name of Account Owner {$id}",
    ]);
    $api->api_services_create([
      'id' => "my_name_{$id}_".($id+2000),
      'account' => "{$id}",
      'up_speed' => 2000,
      'down_speed' => 10000,
    ]);
  }
}
p($api->_api_list('accounts'));
p($api->_api_list('services'));
