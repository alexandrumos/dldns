#!/usr/bin/php
<?php

use League\CLImate\CLImate;
use Dldns\Linode\LinodeApiClient;

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

if (is_null($domainId)) {
    $climate->error('Error: domain_id parameter is missing!');
    $climate->error('E.g. domain_id=012345678');
    exit(0);
}

$client = new LinodeApiClient(getenv('LINODE_PAT'));

$records = $client->get('/v4/domains/' . $domainId . '/records');

$recordsList = [];

foreach ($records as $record) {
    $recordsList[] = $record;
}

$climate->table($recordsList);