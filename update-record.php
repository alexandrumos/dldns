#!/usr/bin/php
<?php

use Dldns\Linode\LinodeApiClient;
use Dldns\IpEcho\IpEchoClient;
use League\CLImate\CLImate;

require_once 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$climate = new CLImate;

$arguments = [];

for ($i = 1; $i < $argc; $i++) {
    $tmp = explode('=', $argv[$i]);
    $arguments[$tmp[0]] = isset($tmp[1]) ? trim($tmp[1]) : null;
}

$domainId = isset($arguments['domain_id']) ? $arguments['domain_id'] : null;
$recordId = isset($arguments['record_id']) ? $arguments['record_id'] : null;
$targetIp = isset($arguments['target_ip']) ? $arguments['target_ip'] : IpEchoClient::getExternalIp();

if (is_null($domainId)) {
    $climate->error('Error: domain_id parameter is missing!');
    $climate->error('E.g. domain_id=012345678');
    exit(0);
}

if (is_null($recordId)) {
    $climate->error('Error: record_id parameter is missing!');
    $climate->error('E.g. record_id=012345678');
    exit(0);
}

$client = new LinodeApiClient(getenv('LINODE_PAT'));

$urlLinodeApi = '/v4/domains/' . $domainId . '/records/' . $recordId;
$payload = [ 'target' => $targetIp ];

$response = $client->put($urlLinodeApi, $payload);
$climate->table([ $response ]);