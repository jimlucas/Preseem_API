<?php

function p($in) {
  if ( !empty($in) ) {
  	if ( is_object($in) || is_array($in) )
  		print_r($in);
  	else
	  	print($in.PHP_EOL);
  } 
}

