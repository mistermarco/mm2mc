<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Parser;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

// Parse the configuration settings
$yaml = new Parser();
$config = $yaml->parse(file_get_contents(__DIR__ . '/app/config/config.yml'));

// General Settings
$app_name = $config['general']['name'];

// MailChimp Settings
$api_key = $config['mc']['api_key'];
$list_id = $config['mc']['list_id'];

// Database / Entity Configuration
$paths = array('/src');
$isDevMode = FALSE;
$dbConfig = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

// Database Connection
$dbParams = array(
    'driver'   => $config['db']['driver'],
    'user'     => $config['db']['user'],
    'password' => $config['db']['password'],
    'dbname'   => $config['db']['name'],
    'host'     => $config['db']['host'],
);

$em = EntityManager::create($dbParams, $dbConfig);

// The MailChimp API URL is based on the API Key and the API version
// Keys end with a -usX (where X is a number). The URL is then usX.api.mailchimp.com
$matches = array();
$mc_api_url = '';

if (preg_match('/.*\-(us.*)$/', $api_key, $matches)) {
  $mc_api_url = 'https://' . $matches[1] . '.api.mailchimp.com/' . $config['mc']['api_version'] . '/';
} else {
  echo "Could not create URL from API key.\n";
  exit;
}

// Set up Guzzle Client which we'll use to connect to the MailChimp API
$client = new Client([
  'base_url' => $mc_api_url,
  'timeout' => 2.0,
  'defaults' => [
    'headers' => ['Authorization' => "apikey $api_key"],
  ],
  // Use the 'X-Trigger-Error' => 'InternalServerError' header to test server errors
]);

$slack = new Slack(
  $config['slack']['endpoint'],
  array(
    'username' => $config['slack']['username'],
    'icon'     => $config['slack']['icon'],
    'channel'  => $config['slack']['channel'],
  )
);

