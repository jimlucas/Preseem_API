<?php

include_once __DIR__ .'/../init.php';

mylog('INFO', 'Freeside is initializing an export...');

mylog('DEBUG', $argv);

$object = null;
$action = null;
$allowed_actions = array('insert', 'delete', 'replace', 'suspend', 'unsuspend');

$longopts = array(
  'access_points' => array('old_ap_id::', 'ap_id:', 'ap_name:', 'ap_tower:', 'ap_ip_address:', ),
  'accounts'      => array('old_account_id::', 'account_id:', 'account_name:', ),
  'packages'      => array('old_package_id::', 'package_id:', 'package_name:', 'package_speed_up::', 'package_speed_down::', ),
  'services'      => array('old_service_id::', 'old_account_id::', 'service_id:', 'service_account:', 'service_speed_up::', 'service_speed_down::', 'service_package::', 'service_parent_device_id::', 'service_ip_address::', 'service_mac_address::', ),
  'sites'         => array('old_site_id::', 'site_id:', 'site_name:', 'site_attachment::', ),
);

/**

# For the purpose of this script and how it is being called from Freeside, specifiying the object is not required.

$allowed_objects = array_keys($longopts);

# check that a valid object was specified
$options = getopt('', array('object:'));
if ( $options === false || empty($options) ) {
  mylog('FATAL', 'Object not specified') && usage();
}
$object = $options['object'];
if ( !in_array($object, $allowed_objects) ) {
  mylog('FATAL', "Object not recognized: \"{$object}\"") && usage();
}
mylog('INFO', "Object = {$object}");

**/


# check that a valid action was specified
$options = getopt('', array('action:'));
if ( $options === false || empty($options) ) {
  mylog('FATAL', 'Action not specified');
  usage();
}
$action = $options['action'];
if ( !in_array($action, $allowed_actions) ) {
  mylog('FATAL', "Action not recognized: \"{$action}\"") && usage();
}
mylog('DEBUG', "Action = {$action}");

foreach ( $longopts AS $name => $options ) {
  # Get the key/value pairs passed to the script for the Account Object
  ${"{$name}_options"} = getopt('', $options);
  mylog('DEBUG', $name.' Object Arguments Received: ' .json_encode(${"{$name}_options"}));
}
# Get the key/value pairs passed to the script for the Service Object
#$services_options = getopt('', $longopts['services']);
#mylog('DEBUG', 'Service Object Arguments Received: ' .json_encode($services_options));

$api = new Preseem();

$text = null;

switch ($action) {

  case 'replace':
    # Delete old account object
    empty($accounts_options['old_account_id']) && mylog('FATAL', '"old_account_id" must be specified') && usage();
    mylog('INFO', "Deleting old account: {$accounts_options['old_account_id']}");
    $api->_api_delete('accounts', $accounts_options['old_account_id']);

    # Delete old service object
    empty($services_options['old_service_id']) && mylog('FATAL', '"old_service_id" must be specified') && usage();
    mylog('INFO', "Deleting old service: {$services_options['old_service_id']}");
    $api->_api_delete('services', $services_options['old_service_id']);

    alternate($text, 'Modify existing');

  case 'insert':

    alternate($text, 'Creating new');

    if ( empty($accounts_options['account_id']) ) { mylog('FATAL', '"account_id" must be specified'); usage(); }
    if ( empty($accounts_options['account_name']) ) { mylog('FATAL', '"account_name" must be specified'); usage(); }

    mylog('INFO', "{$text} account: {$accounts_options['account_id']}");
    $api->api_accounts_create([
      'id' => $accounts_options['account_id'],
      'name' => $accounts_options['account_name'],
    ]);

  case 'suspend':
    alternate($text, 'Suspending');
  case 'unsuspend':
    alternate($text, 'Unsuspending');

    if ( empty($services_options['service_id']) ) { mylog('FATAL', '"service_id" must be specified'); usage(); }
    if ( empty($services_options['service_speed_up']) ) { mylog('FATAL', '"service_speed_up" must be specified'); usage(); }
    if ( empty($services_options['service_speed_down']) ) { mylog('FATAL', '"service_speed_down" must be specified'); usage(); }
    
    mylog('INFO', "{$text} service: {$services_options['service_id']}");
    $api->api_services_create([
      'id' => $services_options['service_id'],
      'account' => $accounts_options['account_id'],
      'up_speed' => intval($services_options['service_speed_up']),
      'down_speed' => intval($services_options['service_speed_down']),
    ]);

    break;

  case 'delete':
    mylog('INFO', "Deleting Service: {$services_options['service_id']}");
    $api->_api_delete('services', $services_options['service_id']);
    break;

  default:
    # do nothing
}

mylog('INFO', 'DONE');

exit(0);

function usage() {

global $author_name, $author_email, $app_version, $app_url;

echo <<<DOC

Usage for: provision.php

  provision.php --action=value [ options ]

Requred options each time this script is called:

  action  What we are doing with each object

Section: service

  Required:

    id      

Author: {$author_name} ({$author_email}) version:{$app_version}    Repo: {$app_url}

DOC;
  die(127);
}

