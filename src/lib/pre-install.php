<?php

// ----------------------------------------------------------------------
// PREAMBLE
//
// Sets up some well-known values for the configuration environment.
//
// Most likely do not need to edit anything in this section.
// ----------------------------------------------------------------------

include_once 'install-funcs.inc.php';

// set default timezone
date_default_timezone_set('UTC');

$OLD_PWD = $_SERVER['PWD'];

// work from lib directory
chdir(dirname($argv[0]));

if ($argv[0] === './pre-install.php' || $_SERVER['PWD'] !== $OLD_PWD) {
  // pwd doesn't resolve symlinks
  $LIB_DIR = $_SERVER['PWD'];
} else {
  // windows doesn't update $_SERVER['PWD']...
  $LIB_DIR = getcwd();
}

if (count($argv) > 1 && $argv[1] === '--non-interactive') {
  $NO_PROMPT = true;
} else {
  $NO_PROMPT = false;
}

$APP_DIR = dirname($LIB_DIR);
$CONF_DIR = $APP_DIR . DIRECTORY_SEPARATOR . 'conf';

$CONFIG_FILE = $CONF_DIR . DIRECTORY_SEPARATOR . 'config.ini';
$APACHE_CONFIG_FILE = $CONF_DIR . DIRECTORY_SEPARATOR . 'httpd.conf';


// ----------------------------------------------------------------------
// CONFIGURATION
//
// Define the configuration parameters necessary in order
// to install/run this application. Some basic parameters are provided
// by default. Ensure that you add matching keys to both the $DEFAULTS
// and $HELP_TEXT arrays so the install process goes smoothly.
//
// This is the most common section to edit.
// ----------------------------------------------------------------------

$DEFAULTS = array(
  'APP_DIR' => $APP_DIR,
  'DATA_DIR' => str_replace('/apps/', '/data/', $APP_DIR),
  'MOUNT_PATH' => '',

  'DB_DSN' => 'pgsql:host=localhost;port=5432;dbname=earthquake',
  'DB_SCHEMA' => 'usdesign',
  'DB_USER' => '',
  'DB_PASS' => ''
);

$HELP_TEXT = array(
  'APP_DIR' => 'Absolute path to application root directory',
  'DATA_DIR' => 'Absolute path to application data directory',
  'MOUNT_PATH' => 'Url path to application',

  'DB_DSN' => 'Database connection DSN string',
  'DB_SCHEMA' => 'Database schema',
  'DB_USER' => 'Read-only username for database connections',
  'DB_PASS' => 'Password for database user'
);


// ----------------------------------------------------------------------
// MAIN
//
// Run the interactive configuration and write configuration files to
// to file system (httpd.conf and config.ini).
//
// Edit this section if this application requires additional installation
// steps such as setting up a database schema etc... When editing this
// section, note the helpful install-funcs.inc.php functions that are
// available to you.
// ----------------------------------------------------------------------

include_once 'configure.php';


// output apache configuration
file_put_contents($APACHE_CONFIG_FILE, '
  # auto generated by ' . __FILE__ . ' at ' . date('r') . '
  Alias ' . $CONFIG['MOUNT_PATH'] . '/data ' . $CONFIG['DATA_DIR'] . '
  Alias ' . $CONFIG['MOUNT_PATH'] . ' ' . $CONFIG['APP_DIR'] . '/htdocs

  <Location ' . $CONFIG['MOUNT_PATH'] . '>
    Order Allow,Deny
    Allow from all
  </Location>

  RewriteEngine on
  RewriteRule ^' . $CONFIG['MOUNT_PATH'] .
    '/service/?([^/]+)?/?([^/]+)?/?([^/]+)?/?([^/]+)?/?([^/]+)?/?([^/]+)? ' .
    $CONFIG['MOUNT_PATH'] . '/service.php?design_code_id=$1&site_class_id=$2' .
    '&risk_category_id=$3&longitude=$4&latitude=$5&title=$6 [L,PT]
');


// configure database
echo "\n";
if (promptYesNo('Would you like to setup the database or load data', false)) {
  include_once 'install/setup_database.php';
}
