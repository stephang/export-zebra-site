#!/usr/bin/php
<?php
require 'vendor/autoload.php';

// ENVIRONMENT CONFIGURATION
$APACHE_VHOST_PATH = "/etc/apache2/sites-enabled";

// Option definition.
$cmdOptions = new Commando\Command();

// Define first option
$cmdOptions->option()
        ->require()
        ->aka('site_path')
        ->describedAs('Site path you want to export (e.g. /var/www/myproject');

// Define a boolean flag "-c" aka "--capitalize"
$cmdOptions->option('v')
        ->aka('verbose')
        ->describedAs('Verbose output')
        ->boolean();


$verbose = $cmdOptions['verbose'];
$site_path = $cmdOptions[0];
if ($verbose)
  echo "Exporting $site_path" . PHP_EOL;

$pattern_server_name = '/^\s*ServerName ([a-zA-Z0-9.-]+)/';

foreach (glob($APACHE_VHOST_PATH . '/*') as $filename) {
  if ($verbose) 
    echo "Checking $filename" . PHP_EOL;
  
  $handle = fopen($filename, "r");
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      if (!isset($server_name)) {
        $server_name = findPattern($pattern_server_name, $line);
      }
    }
  } else {
    echo "Error opening $filename ", PHP_EOL;
  }

  echo $server_name . PHP_EOL;
  unset($server_name);
  //print_r($file);
}

function findPattern($pattern, $input) {
  $matches = array();
  preg_match($pattern, $input, $matches);
  if (isset($matches[1])) {
    return $matches[1];
  }
}
?>
