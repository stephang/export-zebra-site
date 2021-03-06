#!/usr/bin/php
<?php

// ENABLE this line if you want to use Command library
// require 'vendor/autoload.php';

// TODO: Have a warning if there is more than one vhost config file. 
// TODO: Move to drush module

// ENVIRONMENT CONFIGURATION
$APACHE_VHOST_PATH = "/etc/apache2/sites-enabled";

/**
 * Parse command line options.
 */
function parseCliOptions() {
  // ENABLE the following line you want to use the Command library.
  // $cmdOptions = new Commando\Command();

  // Define first option
  $cmdOptions->option()
          ->require()
          ->aka('site_path')
          ->describedAs('Site path you want to export (e.g. /var/www/myproject)');

  // Define a boolean flag "-c" aka "--capitalize"
  $cmdOptions->option('v')
          ->aka('verbose')
          ->describedAs('Verbose output')
          ->boolean();

  global $verbose;
  $verbose = $cmdOptions['verbose'];

  global $site_path;
  $site_path = $cmdOptions[0];
}

/**
 * Parse command line options (currently only file path)
 */
function parseCliOptions2(){
  global $argv;
  
  if ( isset($argv[1]) ) {
    global $site_path;
    $site_path = $argv[1];
  }
  
  global $verbose;
  $verbose = false;
}

/**
 * Returns the vhost configuration for the given project path.
 * 
 * @param type $site_path   Code and doc root of the project
 * @return type array
 */
function parseApacheConfig($site_path) {
  global $APACHE_VHOST_PATH;
  global $verbose;
  
  $config_items = array(
      'server_name'     => '/^\s*ServerName ([a-zA-Z0-9.\-]+)/',
      'doc_root'        => '/^\s*DocumentRoot "?([a-zA-Z0-9.\-\/]+)"?/',
      'ssl_cert'        => '/^\s*SSLCertificateFile ([a-zA-Z0-9.\-\/]+)/',
      'ssl_key'         => '/^\s*SSLCertificateKeyFile ([a-zA-Z0-9.\-\/]+)/',
      'ssl_cert_ca'     => '/^\s*SSLCertificateChainFile ([a-zA-Z0-9.\-\/]+)/',     
  );
    
  foreach (glob($APACHE_VHOST_PATH . '/*') as $filename) {
    if ($verbose) 
      echo "Checking $filename" . PHP_EOL;

    $handle = fopen($filename, "r");
    if ($handle) {
      $vhost_config = array();
      $vhost_config['config_file'] = realpath($filename);
      
      while (($line = fgets($handle)) !== false) {
        foreach ($config_items as $item => $pattern) {
          if (!isset($vhost_config[$item])) {
            $vhost_config[$item] = findPattern($pattern, $line);
          }
        }
      }
      
      // echo 'if ' . realpath($vhost_config['doc_root']) . ' == ' . realpath($site_path) . PHP_EOL;
      if (realpath($vhost_config['doc_root']) == realpath($site_path)) {
        return $vhost_config;
      }
    } else {
      echo "Error opening $filename ", PHP_EOL;
    }
  }
}

function findPattern($pattern, $input) {
  $matches = array();
  preg_match($pattern, $input, $matches);
  if (isset($matches[1])) {
    return $matches[1];
  }
}

// The following function works only with PHP 5.3  :(
// parseCliOptions()

parseCliOptions2();

if ($verbose)
  echo "Exporting $site_path" . PHP_EOL;

print_r(parseApacheConfig($site_path));

// CREATE ARCHIVE
//  copy SSL files (and rename to HOSTNAME.crt|.key
//  create db dump 'db-dump.mysql.gz' using BAM
//  create archive: tar -cf leises.berlin.de.tgz db-dump.mysql.gz htdocs/ ssl/
?>
