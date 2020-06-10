<?php

function mylog($error_level='info', $message) {
  global $timestamp;
  global $pid;
  global $logfile;

  if ( is_array($message) )
    $message = json_encode($message);

  $suffix = date($timestamp) . " [ {$pid} ] - [ {$error_level} ] - ";

  $line = $suffix.$message.PHP_EOL;

  if ( defined('DEBUG') ) {
    if ( strtoupper($error_level) === 'DEBUG' ) {
      if  ( DEBUG ) {
        echo $line;
      } else {
        $line = '';
      }
    } 
  }

  !empty($line) && file_put_contents($logfile, $line, FILE_APPEND);

  if ( strtoupper($error_level) === 'FATAL' ) {
    echo $line;
    foreach (debug_backtrace() AS $k => $v) {
      $line = $suffix . "#{$k}  ".(isset($v['class'])?$v['class'].'->':'')."{$v['function']}(".json_encode($v['args']).") called at [{$v['file']}:{$v['line']}]" . PHP_EOL;
#      echo $line;
      file_put_contents($logfile, $line, FILE_APPEND);
    }
  }
}

