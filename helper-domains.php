#!/usr/bin/php
<?php

use League\CLImate\CLImate;
use Dldns\Linode\LinodeApiClient;

require_once 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$climate = new CLImate;

$client = new LinodeApiClient(getenv('LINODE_PAT'));

$domains = $client->get('/v4/domains');

$domainsList = [];

foreach ($domains as $domain) {
    $domainsList[] = [
        'id' => $domain->id,
        'domain' => $domain->domain
    ];
}

$climate->table($domainsList);