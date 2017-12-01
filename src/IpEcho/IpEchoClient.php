<?php

namespace Dldns\IpEcho;

use GuzzleHttp\Client;

class IpEchoClient {

    const ipEchoUrl = 'http://ipecho.net';

    /**
    * Performs a GET request to ipecho.net service and returns the client IP as string
    *
    * @return string
    */
    public static function getExternalIp()
    {
        $client = new Client(['base_uri' => self::ipEchoUrl]);
        $response = $client->request('GET', 'plain');
        $responseBody = $response->getBody();

        return $responseBody->getContents();
    }

}