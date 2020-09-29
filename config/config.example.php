<?php

$author_name = 'Jim Lucas';
$author_email = 'jlucas@cmsws.com';
$app_version = '0.0.1';
$app_url = 'https://github.com/jimlucas/Preseem_API';

# Locationof log file
$logfile = ROOT_PATH.'logs/preseem.log';

# Process ID - to be used in the log entries
$pid = getmypid();

# Timestamp format used 
# Any format that is valid for date() can be used here
$timestamp = 'Y/m/d H:i:s';

# Secret API key from Preseem
$api_key = '';

# Preseem API URL (default)
$api_url = 'https://api.preseem.com/model/v1/';

# Enable debugging, if this define is missing (no DEBUG constant is defined) then all DEBUG message will be dumped to the log, but not STDOUT
# false = No debugging to STDOUT or to the log file
# true = Debugging will be sent to both the log file and STDOUT
define('DEBUG', false);

