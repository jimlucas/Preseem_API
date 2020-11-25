<?php

include_once __DIR__ . '/../init.php';

mylog('INFO', 'Freeside is initializing an export...');

#mylog('DEBUG', $argv);
(METHOD === 'cli' ) ? mylog('DEBUG', $argv):'';

$object = null;
$action = null;
$allowed_actions = array('insert', 'delete', 'replace', 'suspend', 'unsuspend');

$longopts = array(
  'access_points' => array('old_ap_id::', 'ap_id:', 'ap_name:', 'ap_tower:', 'ap_ip_address:', ),
  'accounts'      => array('old_account_id::', 'account_id::', 'account_name:', 'custnum:', 'pkgnum:', 'old_pkgnum:', 'use_api:', ),
  'packages'      => array('old_package_id::', 'package_id::', 'package_name:', 'package_up_speed::', 'package_down_speed::', ),
  'services'      => array('old_service_id::', 'service_id::', 'service_account:', 'service_up_speed::', 'service_down_speed::', 'service_package::', 'service_parent_device_id::', 'service_network_prefixes::', 'service_cpe_mac::', 'pkgnum:', 'old_pkgnum:', 'old_account_id::', 'use_api:', ),
  'sites'         => array('old_site_id::', 'site_id:', 'site_name:', 'site_network_prefixes::', ),
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
#$options = my_getopt('', array('action:'));
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
    alternate($text, 'Modify existing');

    # Delete old account object
    if ( empty($accounts_options['old_account_id']) ) { mylog('FATAL', '"old_account_id" must be specified'); usage(); }
    $accounts_options['old_account_id'] = trim($accounts_options['old_account_id']);
    $api->_api_delete('accounts', $accounts_options['old_account_id']);

    # Delete old service object
    if ( empty($accounts_options['use_api']) ) {
      if ( empty($services_options['old_service_id']) ) { mylog('FATAL', '"old_service_id" must be specified or obtained via the Freeside API'); usage(); }
      $services_options['old_service_id'] = trim($services_options['old_service_id']);
    } else {
      if ( empty($services_options['pkgnum']) ) { mylog('FATAL', 'When using the "use_api" option "pkgnum" must be specified'); usage(); }
      if ( ( $service_id = get_customer_package_name($accounts_options['account_id'], $services_options['pkgnum']) ) !== false ) {
        $services_options['old_service_id'] = trim($services_options['pkgnum'].' - '.$service_id);
      } else {
        mylog('FATAL', '"old_service_id" must be specified or obtained via the Freeside API'); usage();
      }
    }

    mylog('INFO', "Deleting old service: {$services_options['old_service_id']}");
    $api->_api_delete('services', $services_options['old_service_id']);

  case 'insert':
    alternate($text, 'Creating new');

    if ( empty($accounts_options['account_id']) ) { mylog('FATAL', '"account_id" must be specified'); usage(); }
    $accounts_options['account_id'] = trim($accounts_options['account_id']);
    if ( empty($accounts_options['use_api']) ) {
      if ( empty($accounts_options['account_name']) ) { mylog('FATAL', '"account_name" must be specified or obtained via the Freeside API'); usage(); }
      $accounts_options['account_name'] = trim($accounts_options['account_name']);
    } else {
      if ( empty($accounts_options['custnum']) ) { mylog('FATAL', 'When using --use_api the "--custnum" must be specified'); usage(); }
      if ( ($account_name = get_customer_name($accounts_options['custnum'])) !== false ) {
        $accounts_options['account_name'] = trim($account_name);
      } else {
        mylog('FATAL', '"account_name" must be specified or obtained via the Freeside API'); usage();
      }
    }

    mylog('INFO', "{$text} account: {$accounts_options['account_id']}, {$accounts_options['account_name']}");
    $api->api_accounts_create([
      'id' => $accounts_options['account_id'],
      'name' => $accounts_options['account_name'],
    ]);

  case 'suspend':
    alternate($text, 'Suspending');

  case 'unsuspend':
    alternate($text, 'Unsuspending');

    if ( empty($accounts_options['account_id']) ) { mylog('FATAL', '"account_id" must be specified'); usage(); }
    $accounts_options['account_id'] = trim($accounts_options['account_id']);

    if ( empty($accounts_options['use_api']) ) {
      if ( empty($services_options['service_id']) ) { mylog('FATAL', '"service_id" must be specified or obtained via the Freeside API'); usage(); }
      $services_options['service_id'] = trim($services_options['service_id']);
    } else {
      if ( empty($services_options['pkgnum']) ) { mylog('FATAL', 'When using the "use_api" option "pkgnum" must be specified'); usage(); }
      if ( ( $service_id = get_customer_package_name($accounts_options['account_id'], $services_options['pkgnum']) ) !== false ) {
        $services_options['service_id'] = trim($services_options['pkgnum'].' - '.$service_id);
      } else {
        mylog('FATAL', '"service_id" must be specified or obtained via the Freeside API'); usage();
      }
    }


    if ( empty($services_options['service_up_speed']) ) { mylog('FATAL', '"service_up_speed" must be specified'); usage(); }
    if ( empty($services_options['service_down_speed']) ) { mylog('FATAL', '"service_down_speed" must be specified'); usage(); }

    if ( !empty($services_options['service_network_prefixes']) ) {
      $attachments = new stdClass();
      if ( filter_var($services_options['service_network_prefixes'], FILTER_VALIDATE_IP) ) {
        $attachments->network_prefixes = explode(',', $services_options['service_network_prefixes']);
      } else {
        mylog('FATAL', 'Service IP address provided but is not valid');
        usage();
      }

    }

    if ( !empty($services_options['service_cpe_mac']) ) {
      $attachments = (isset($attachments) ? $attachments : new stdClass());
      if ( filter_var($services_options['service_cpe_mac'], FILTER_VALIDATE_MAC) ) {
        $attachments->cpe_mac = strtolower($services_options['service_cpe_mac']);
      } elseif ( preg_match('/^[a-f0-9]{12}$/i', $services_options['service_cpe_mac']) ) {
        $attachments->cpe_mac = strtolower(join(':', str_split($services_options['service_cpe_mac'], 2)));
      } else {
        mylog('FATAL', 'Service MAC addr provided but does not validated');
        usage();
      }
    }

    # This is a duplicate check from the insert section. But, that section is 
    # not called if we are only doing a suspend or unsuspend action.
    if ( empty($accounts_options['account_id']) ) { mylog('FATAL', '"account_id" must be specified'); usage(); }

    mylog('INFO', "{$text} service: ".$services_options['service_id']);

    $obj = array(
      'id' => $services_options['service_id'],
      'up_speed' => intval($services_options['service_up_speed']),
      'down_speed' => intval($services_options['service_down_speed']),
      'account' => $accounts_options['account_id'],
    );

    if ( isset($attachments) )
      $obj['attachments'] = array($attachments);

    if ( isset($services_options['service_package']) )
      $obj['package'] = $services_options['service_package'];

    if ( isset($services_options['service_parent_device_id']) )
      $obj['parent_device_id'] = $services_options['service_parent_device_id'];

    $api->api_services_create($obj);

    break;

  case 'delete':

    if ( empty($accounts_options['account_id']) ) { mylog('FATAL', '"account_id" must be specified'); usage(); }
    $accounts_options['account_id'] = trim($accounts_options['account_id']);
    mylog('INFO', "Deleting Account: {$accounts_options['account_id']}");
    $api->_api_delete('accounts', $accounts_options['account_id']);

    if ( empty($accounts_options['use_api']) ) {
      if ( empty($services_options['service_id']) ) { mylog('FATAL', '"service_id" must be specified or obtained via the Freeside API'); usage(); }
      $services_options['service_id'] = trim($services_options['service_id']);
    } else {
      if ( empty($services_options['pkgnum']) ) { mylog('FATAL', 'When using the "use_api" option "pkgnum" must be specified'); usage(); }
      if ( ( $service_id = get_customer_package_name($accounts_options['account_id'], $services_options['pkgnum']) ) !== false ) {
        $services_options['service_id'] = trim($services_options['pkgnum'].' - '.$service_id);
      } else {
        mylog('FATAL', '"service_id" must be specified or obtained via the Freeside API'); usage();
      }
    }
#    if ( empty($services_options['service_id']) ) { mylog('FATAL', '"service_id" must be specified'); usage(); }
#    $services_options['service_id'] = trim($services_options['service_id']);
    mylog('INFO', "Deleting Service: ".$services_options['service_id']);
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


Author: {$author_name} ({$author_email}) version:{$app_version}    Repo: {$app_url}

DOC;
die();
}


function api_call($method, $args=array()) {
  global $FS_API_KEY;
  $args[] = 'secret';
  $args[] = $FS_API_KEY;
  $request = xmlrpc_encode_request($method, $args);

  $fp = fsockopen('localhost', 8008, $errno, $errstr);
  $query = "POST {$method} HTTP/1.0\nUser_Agent: PHP\nHost: localhost\nContent-Type: text/xml\nContent-Length: ".strlen($request)."\n\n".$request."\n";

  if (!fputs($fp, $query, strlen($query))) {
    $errstr = "Write error";
    return 0;
  }

  $headers = array();
  while ( $h = fgets($fp) ) {
    if ( $h === "\r\n" )
      break;
    $headers[] = $h;
  }

  $contents = '';
  while (!feof($fp)) {
    $contents .= fgets($fp);
  }

  fclose($fp);
  return array(xmlrpc_decode($contents, 'UTF-8'), $headers);
}

function get_customer_name($custnum) {
  $results = api_call('FS.API.customer_info', ['custnum', $custnum]);
  if (!empty($results[0]['company']) ) {
    return $results[0]['company'];
  } elseif (!empty($results[0]['name']) ) {
    return $results[0]['name'];
  } elseif (!empty($results[0]['first']) or !empty($results[0]['last']) ) {
    return "{$results[0]['last']},{$results[0]['first']}";
  }
  return 'Customer Name not found: Customer Number = '.$custnum;
}

function get_customer_package_name($custnum, $pkgnum) {
  $results = api_call('FS.API.list_customer_packages', ['custnum', $custnum]);
  foreach ($results[0]['packages'] AS $package) {
    if ( $package['pkgnum'] == $pkgnum ) {
      return $package['pkg'];
    }
  }
  return 'Package Name not found: Package ID = '.$pkgnum;
}
