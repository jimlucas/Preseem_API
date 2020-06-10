<?php

foreach (['access_points', 'accounts', 'packages', 'services', 'sites'] AS $verb) {

  $descr = ucfirst(str_replace('_', ' ', $verb));
  $api_responses[$verb]['LIST'] = array(
    200 => $descr.' returned',
    401 => 'API key does not have the required permission',
    500 => 'Server cannot process request',
  );
  
  $descr = preg_replace('/s$/', '', $descr);
  $api_responses[$verb]['CREATE'] = array(
    200 => $descr.' added',
    400 => 'Required json field missing in the PUT request OR ID in the URI does not match the id in json OR Bad json',
    401 => 'API key does not have the required permission',
    500 => 'Server cannot process request',
  );

  $api_responses[$verb]['DELETE'] = array(
    200 => $descr.' deleted',
    400 => 'Missing ID in URI',
    401 => 'API key does not have the required permission',
    404 => $descr.' does not exist',
    500 => 'Server cannot process request',
  );

  $api_responses[$verb]['GET'] = array(
    200 => $descr.' returned',
    400 => 'Missing ID in URI',
    401 => 'API key does not have the required permission',
    404 => $descr.' not found',
    500 => 'Server cannot process request',
  );

}

#print_r($api_responses);