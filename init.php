<?php

define('ROOT_PATH', __DIR__ .'/');

foreach ( glob(ROOT_PATH.'config/config\.*\.php') AS $filename ) {
  if ( ROOT_PATH.'config/config.local.php' !== $filename )
    include_once $filename;
}

if ( !is_file( ROOT_PATH.'config/config.local.php') )
  die('ERROR: Missing Config File; '.ROOT_PATH.'config/config.local.php');
include_once ROOT_PATH.'config/config.local.php';

include_once ROOT_PATH.'functions/common.php';
include_once ROOT_PATH.'functions/io.php';
include_once ROOT_PATH.'functions/log.php';
include_once ROOT_PATH.'classes/api.responses.php';
include_once ROOT_PATH.'classes/class.preseem.php';

