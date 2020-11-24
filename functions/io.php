<?php

function my_getopt($options, $longopts=array(), &$optind=false) {
  if (PHP_SAPI === 'cli') {
    return getopt($options, $longopts, $optind);
  } else {
    return getopt_http($options, $longopts, $optind);
  }
  return false;
}

function getopt_http($options, $longopts=array(), &$optind=false) {

  $fields = array_merge(chunk_split($options, 1), $longopts);

  return join(',', $fields);

  if ( METHOD === 'POST' ) {
    $IN = $_POST;
  } else if ( METHOD === 'GET' ) {
    $IN = $_GET;
  } else {
    return false;
  }

  foreach ( $IN AS $k => $v ) {
#    if ( !in_array($

    

  }

  return false;
}

function p($in) {
  if ( !empty($in) ) {
  	if ( is_object($in) || is_array($in) )
  		print_r($in);
  	else
	  	print($in.PHP_EOL);
  } 
}

