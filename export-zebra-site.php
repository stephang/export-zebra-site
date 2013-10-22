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
  
  $pattern_server_name = '/^\s*ServerName ([a-zA-Z0-9.\-]+)/';
  $pattern_ssl_cert = '/^\s*SSLCertificateFile ([a-zA-Z0-9.\-\/]+)/';
  $pattern_ssl_key = '/^\s*SSLCertificateKeyFile ([a-zA-Z0-9.\-\/]+)/';
  $pattern_ssl_cert_ca = '/^\s*SSLCertificateChainFile ([a-zA-Z0-9.\-\/]+)/';
  $pattern_doc_root = '/^\s*DocumentRoot "?([a-zA-Z0-9.\-\/]+)"?/';
  
  foreach (glob($APACHE_VHOST_PATH . '/*') as $filename) {
    if ($verbose) 
      echo "Checking $filename" . PHP_EOL;

    $handle = fopen($filename, "r");
    if ($handle) {
      $vhost_config = array();
      
      while (($line = fgets($handle)) !== false) {
        if (!isset($vhost_config['server_name'])) {
          $vhost_config['server_name'] = findPattern($pattern_server_name, $line);
        }
        if (!isset($vhost_config['doc_root'])) {
          $vhost_config['doc_root'] = findPattern($pattern_doc_root, $line);
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
