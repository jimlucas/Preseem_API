<?php

/**
 * Preseem API (Version 1)
 * 
 * This file provides an interface to the API used to provision a Preseem appliance
 * @author Jim Lucas <jlucas@cmsws.com>
 * @version 0.0.1
 * @package preseem_api
 *
 * **HTTP Methods**
 * 
 * This API supports the following HTTP methods: `DELETE`, `GET`, `PUT`
 * The documentation below provide details on when and how to use these methods.
 * 
 * **Content-Type**
 * 
 * For `PUT` requests, you must include `Content-Type` in the HTTP Header and it should be set to `application/json`, otherwise a 400 (`Bad Request`) is returned. 
 * The API only supports `JSON`
 * 
 * **API Key Permissions**
 * 
 * The permissions required for this API are: 
 * - `network-metadata-read`
 * - `network-metadata-write`
 * 
 * `network-metadata-read` is needed for `GET` HTTP request
 * 
 * `network-metadata-write` is needed for `DELETE` and `PUT` HTTP requests
 * 
 * **Encoding**
 * 
 * URI parameters must be URL encoded. 
 * 
 * For example to get an account with the id of `john/doe`, `/model/v1/accounts/john/doe` will fail and return a `404` error.
 * The correct way will be `/model/v1/accounts/john%2Fdoe`. Notice `/` in the id is replaced with the encoded form `%2F`
 * 
 * **Note**
 * 
 * All IDs are case-sensitive, `John Doe` and `John doe` could be the IDs of two different accounts
 * 
 * # Authentication
 * 
 * All requests are secured using basic auth. Your API key is the username, the password is empty.
 * 
 * A sample HTTP Header is `Authorization: Basic YXNydjY1XzBtaERzYVFXa3U6`
 * 
 * A sample request using cURL, to get all access points will be:
 * `curl -u asrv65_0mhDsaQWku: https://api.preseem.com/model/v1/access_points`
 * 
 * The API key and the colon must be base64 encoded
 * 
 */

class Preseem {

  private $api_url = '';

  private $api_key = '';

  function __construct(){
    global $api_url;
    $this->api_url = $api_url;
    mylog('DEBUG', 'API URL: '.$this->api_url);
    global $api_key;
    $this->api_key = $api_key;
    mylog('DEBUG', 'API Key: '.$this->api_key);
    global $api_responses;
    $this->api_responses = $api_responses;
  }

  private function getResponseMessage($verb, $method, $response_code) {
    if ( isset($this->api_responses[$verb][$method][$response_code]) ) {
      return $this->api_responses[$verb][$method][$response_code];
    }
    return 'Unrecognized Response: ' . json_encode(array($verb, $method, $response_code));
  }

  private function __api_send($object = '', $__URI = '', $action = '', $params = array()) {

    $method = 'GET';
    $headers = array();

    empty($object) && mylog('FATAL', 'Object not set');

    empty($__URI) && mylog('FATAL', 'URI not set');

    empty($action) && mylog('FATAL', 'Action not set');

    empty($this->api_url) && mylog('FATAL', 'Server not set. Please use Obj->setServer("server")');

    empty($this->api_key) && mylog('FATAL', 'API Key not set. Please use Obj->setAPIKey("your_key")');

    array_push($headers, 'Content-Type: application/json');

    switch($action) {
      case 'LIST':
        $method = 'GET';
        break;
      case 'CREATE':
        $method = 'PUT';
        break;
      case 'DELETE':
        $method = 'DELETE';
        break;
      case 'GET':
        $method = 'GET';
        break;
      default:
        mylog('FATAL', "Invalid HTTP method: {$method}");
        return false;
        break;        
    }

    /**
     * Inialize the cURL object
     */
    $ch = curl_init();

    /**
     * Set the URL we will be calling to
     */
    curl_setopt($ch, CURLOPT_URL, $this->api_url . $__URI);

    /**
     * Set Username & Password for Basic Auth
     */
    curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':');

    /**
     * Set custom method
     */
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    /**
     * Setup cURL to have the body content when performing a PUT request
     */
    if ( $method === 'PUT' ) {
      $payload = json_encode($params);
      array_push($headers, 'Content-Length: ' . strlen($payload));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    /**
     * Add headers
     */
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    /**
     * Set cURL to follow redirection Location header
     */
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    /**
     * Execute request and capture response
     */
    $data = curl_exec($ch);

    /**
     * Capture last response code
     */
    $response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    /**
     * Close and destroy cURL object
     */
    curl_close($ch);

    mylog('DEBUG', json_encode(['object' => $object, 'action' => $action, 'response_code' => $response_code, 'data' => $data]));

    if ( $response_code != 200 ) {
      mylog('INFO', $this->getResponseMessage($object, $action, $response_code) .' - Data Returned: '. trim($data));
    }

    return json_decode($data);
  }

  function setServer($server='') {
    $this->__server = $server;
    return true;
  }

  function setAPIKey($APIKey='') {
    $this->api_key = $APIKey;
    return true;
  }

  function _api_list($object, $page=1, $limit=500) {
    mylog('INFO', json_encode(['object'=>$object]));
    return $this->__api_send($object, ($object . '?' . http_build_query(['page'=>$page,'limit'=>$limit])), 'LIST');
  }
  function _api_create($object, $params) {
    mylog('INFO', json_encode(['object'=>$object, $params]));
    return $this->__api_send($object, ($object .'/'. rawurlencode($params['id'])), 'CREATE', $params);
  }
  function _api_delete($object, $id) {
    mylog('INFO', json_encode(['object'=>$object, 'id'=>$id]));
    return $this->__api_send($object, ($object .'/'. rawurlencode($id)), 'DELETE');
  }
  function _api_get($object, $id) {
    mylog('INFO', json_encode(['object'=>$object, 'id'=>$id]));
    if ( ( $results = $this->__api_send($object, ($object .'/'. rawurlencode($id)), 'GET') ) === false ) {
      mylog('FATAL', ucfirst($object)." ID: {$id}");
    }
    return $results;
  }
  
  function api_access_points_create($params = array()) {
    $messages = array();
    if ( !array_key_exists('id', $params) || ( isset($params['id']) && !is_string($params['id']) ) ) {
      $messages[] = 'Unique id for access point not set.  Type: String';
    }
    if ( !array_key_exists('name', $params) || ( isset($params['name']) && !is_string($params['name']) ) ) {
      $messages[] = 'Name for access point not set.  Type: String';
    }
    if ( !array_key_exists('tower', $params) || ( isset($params['tower']) && !is_string($params['tower']) ) ) {
      $messages[] = 'Tower for access point not set.  Type: String';
    }
    if ( !array_key_exists('ip_address', $params) || ( isset($params['ip_address']) && !is_string($params['ip_address']) ) ) {
      $messages[] = 'IP Address for access point not set.  Type: String';
    }
    !empty($messages) && mylog('FATAL', 'Missing Data: ' . json_encode($messages));
    return $this->_api_create('access_points', $params);
  }

  function api_accounts_create($params = array()) {
    $messages = array();
    if ( !array_key_exists('id', $params)  || ( isset($params['id']) && !is_string($params['id']) ) ) {
      $messages[] = 'Unique id for account not set.  Type: String';
    }
    if ( !array_key_exists('name', $params)  || ( isset($params['name']) && !is_string($params['name']) ) ) {
      $messages[] = 'Name for account not set.  Type: String';
    }
    !empty($messages) && mylog('FATAL', 'Missing Data: ' . json_encode($messages));
    return $this->_api_create('accounts', $params);
  }

  function api_packages_create($params = array()) {
    $messages = array();
    if ( !array_key_exists('id', $params)  || ( isset($params['id']) && !is_string($params['id']) ) ) {
      $messages[] = 'Unique id for package not set.  Type: String';
    }
    if ( !array_key_exists('name', $params)  || ( isset($params['name']) && !is_string($params['name']) ) ) {
      $messages[] = 'Name for package not set.  Type: String';
    }
    if ( array_key_exists('up_speed', $params) && ( !is_integer($params['up_speed']) || intval($params['up_speed']) < 0) ) {
      $messages[] = 'The upstream rate limit, in Kbps. A value of 0 is treated as not set. If not set, this field is omitted in the returned json. A negative speed returns a 400 error.  Type: Integer';
    }
    if ( array_key_exists('down_speed', $params) && ( !is_integer($params['down_speed']) || intval($params['down_speed']) < 0) ) {
      $messages[] = 'The downstream rate limit, in Kbps. A value of 0 is treated as not set. If not set, this field is omitted in the returned json. A negative speed returns a 400 error.  Type: Integer';
    }
    !empty($messages) && mylog('FATAL', 'Missing Data: ' . json_encode($messages));
    return $this->_api_create('packages', $params);
  }

  function api_services_create($params = array()) {
    $messages = array();
    if ( !array_key_exists('id', $params)  || ( isset($params['id']) && !is_string($params['id']) ) ) {
      $messages[] = 'Unique id for service not set.  Type: String';
    }
    if ( !array_key_exists('account', $params)  || ( isset($params['account']) && !is_string($params['account']) ) ) {
      $messages[] = 'Account id for the service. This id is just a reference to an account, the account doesn\'t have to exist, but when it does, the service gets attached to the account.  Type: String';
    }
    if ( array_key_exists('attachments', $params) ) {
      if ( count($params['attachments']) < 1 ) {
        $messages[] = 'Service attachments array must not be empty';
      }
      foreach($params['attachments'] AS $attachment) {
        if ( !property_exists($attachment, 'cpe_mac') && !property_exists($attachment, 'network_prefixes') ) {
          $messages[] = 'If provided, service attachments objects must contain one property: cpe_mac or network_prefixes.  Type: String';
        }
      }
    }
    if ( array_key_exists('up_speed', $params) && ( !is_integer($params['up_speed']) || intval($params['up_speed']) < 0) ) {
      $messages[] = 'The upstream rate limit, in Kbps. A value of 0 is treated as not set. If not set, this field is omitted in the returned json. A negative speed returns a 400 error.  Type: Integer';
    }
    if ( array_key_exists('down_speed', $params) && ( !is_integer($params['down_speed']) || intval($params['down_speed']) < 0) ) {
      $messages[] = 'The downstream rate limit, in Kbps. A value of 0 is treated as not set. If not set, this field is omitted in the returned json. A negative speed returns a 400 error.  Type: Integer';
    }
    if ( array_key_exists('package', $params) && !is_string($params['package']) ) {
      $messages[] = 'Package id of the service.  Type: String';
    }
    if ( array_key_exists('parent_device_id', $params) && !is_string($params['parent_device_id']) ) {
      $messages[] = 'Parent device id does not exist. This is the unique id for the parent device. E.g. An access point.  Type: String';
    }
    return !empty($messages) ? json_encode($messages) . mylog('FATAL', 'Missing Data: ' . json_encode($messages)) : $this->_api_create('services', $params);
  }

  function api_sites_create($params = array()) {
    $messages = array();
    if ( !array_key_exists('id', $params)  || ( isset($params['id']) && !is_string($params['id']) ) ) {
      $messages[] = 'Unique id for site not set.  Type: String';
    }
    if ( !array_key_exists('name', $params)  || ( isset($params['name']) && !is_string($params['name']) ) ) {
      $messages[] = 'Name for site not set.  Type: String';
    }
    !empty($messages) && mylog('FATAL', 'Missing Data: ' . json_encode($messages));
    return $this->_api_create('sites', $params);
  }
}
