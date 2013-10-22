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


function parseApacheConfig() {
  global $APACHE_VHOST_PATH;
  global $verbose;
  global $site_path;
  
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

print_r(parseApacheConfig());
?>
