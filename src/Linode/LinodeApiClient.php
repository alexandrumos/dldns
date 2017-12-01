<?php

namespace Dldns\Linode;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Exception;

class LinodeApiClient {

    private $personalAccessToken;
    private $client;
    private $apiHost = 'api.linode.com';

    /**
    * Linode API client constructor
    *
    * @param string $personalAccessToken Linode personal access token
    */
    public function __construct($personalAccessToken)
    {
        $this->setPersonalAccessToken($personalAccessToken);

        $this->client = new GuzzleHttpClient();
    }

    /**
    * Set's the Linode personal access token
    *
    * @param string $personalAccessToken Linode personal access token
    */
    public function setPersonalAccessToken($personalAccessToken)
    {
        if (empty($personalAccessToken)) {
            throw new Exception('Personal Access Token is empty. You can generate one in Linode Cloud - My Settings - API Tokens');
        }

        $this->personalAccessToken = $personalAccessToken;
    }

    /**
    * Get's the configured Linode personal access token
    *
    * @return string
    */
    public function getPersonalAccessToken()
    {
        return $this->personalAccessToken;
    }

    /**
    * Performs a GET request to Linode API
    *
    * @param string $uri Resource URI
    * @return string
    */
    public function get($uri)
    {
        return $this->request($uri, 'get', []);
    }

    /**
    * Performs a POST request to Linode API
    *
    * @param string $uri Resource URI
    * @param array  $data Payload
    * @return string
    */
    public function post($uri, $data)
    {
        return $this->request($uri, 'post', $data);
    }

    /**
    * Performs a PUT request to Linode API
    *
    * @param string $uri Resource URI
    * @param array  $data Payload
    * @return string
    */
    public function put($uri, $data)
    {
        return $this->request($uri, 'put', $data);
    }

    /**
    * Performs a DELETE request to Linode API
    *
    * @param string $uri Resource URI
    * @return string
    */
    public function delete($uri)
    {
        return $this->request($uri, 'delete', []);
    }

    /**
    * Handles all the requests into a single place
    *
    * @param string $uri Resource URI
    * @param string $method Request method - can be GET, POST, PUT or DELETE
    * @param array $data Request payload
    * @return string
    */
    private function request($uri, $method, $data = [])
    {
        $method === strtolower(trim($method));
        
        if ($method === 'get') {
            // performing a GET request which requires pagination handling
            return $this->getPaginatedData($uri);
        } elseif ($method === 'post' || $method === 'put') {
            // performing a POST or PUT request which needs a JSON payload
            return $this->sendDataRequest($uri, $method, $data);
        } elseif ($method === 'delete') {
            // performing a DELETE request
            return $this->deleteRequest($uri);
        } else {
            // unknown method
            throw new Exception('Unknown request method: ' . $method);
        }
    }

    /**
    * Performs a POST/PUT requests into a single place
    *
    * @param string $uri Resource URI
    * @param string $method Request method - can be post or put
    * @param array $data Request payload
    * @return string
    */
    private function sendDataRequest($uri, $method, $data)
    {
        $url = 'https://' . $this->apiHost . $uri;

        $response = $this->client->request($method, $url, [
            'headers' => $this->requestHeadersArray(),
            'json' => $data
        ]);

        $statusCode = intval($response->getStatusCode());

        if ($statusCode !== 200) {
            $this->handleErrorResponse($statusCode);
        }

        return $this->handleResponse($response);
    }

    /**
    * Performs a GET request and retrieves a certain page for a resource
    *
    * @param int $pageNo Page number
    * @param string $uri Resource URI
    * @return string
    */
    private function getPage($pageNo, $uri)
    {
        $pageNo = intval($pageNo);

        if ($pageNo === 0) {
            $pageNo = 1;
        }

        $url = 'https://' . $this->apiHost . $uri;
        $url .= '?page='.$pageNo;

        $response = $this->client->request('get', $url, [
            'headers' => $this->requestHeadersArray()
        ]);

        $statusCode = intval($response->getStatusCode());

        if ($statusCode !== 200) {
            $this->handleErrorResponse($statusCode);
        }

        return $this->handleResponse($response);
    }

    /**
    * Performs a DELETE request
    *
    * @param string $uri Resource URI
    * @return bool
    */
    private function deleteRequest($uri)
    {
        $url = 'https://' . $this->apiHost . $uri;

        $response = $this->client->request('delete', $url, [
            'headers' => $this->requestHeadersArray()
        ]);

        $statusCode = intval($response->getStatusCode());
        
        if ($statusCode !== 200) {
            $this->handleErrorResponse($statusCode);
        }

        return true;
    }

    /**
    * Retrieves data for all pages from a GET request
    *
    * @param string $uri Resource URI
    * @return string
    */
    private function getPaginatedData($uri)
    {
        $responseData = $this->getPage(1, $uri);

        $pages = isset($responseData->pages) ? intval($responseData->pages) : 0;
        $data = (array) $responseData->data;

        if ($pages > 1) {
            for ($j = 1; $j <= $pages; $j++) {
                $response = $this->getPage($j, $uri, $method, $postData);
                $data = array_merge($data, $response->data);
            }
        }

        return $data;
    }

    /**
    * Handles the requests response error into a single place based on the request's status code
    *
    * @param int $statusCode Request status code
    */
    private function handleErrorResponse($statusCode)
    {
        // TODO: extend with error messages
        if ($statusCode === LinodeApiClientError::HTTP_ERROR_INVALID_REQUEST) {
            throw new LinodeApiRequestException('You submitted an invalid request (missing parameters, etc)', $statusCode);
        } elseif ($statusCode === LinodeApiClientError::HTTP_ERROR_AUTH_FAIL) {
            throw new LinodeApiRequestException('You failed to authenticate for this resource.', $statusCode);
        } elseif ($statusCode === LinodeApiClientError::HTTP_ERROR_PERMISSION_FAIL) {
            throw new LinodeApiRequestException('You are authenticated, but don\'t have permission to do this.', $statusCode);
        } elseif ($statusCode === LinodeApiClientError::HTTP_ERROR_MISSING_RESOURCE) {
            throw new LinodeApiRequestException('The resource you\'re asking for does not exist .', $statusCode);
        } elseif ($statusCode === LinodeApiClientError::HTTP_ERROR_LIMIT) {
            throw new LinodeApiRequestException('You\'ve hit some sort of rate limit .', $statusCode);
        } elseif ($statusCode === LinodeApiClientError::HTTP_ERROR_SUPPORT) {
            throw new LinodeApiRequestException('Let support know.', $statusCode);
        } else {
            throw new LinodeApiRequestException('You have some sort of error.', $statusCode);
        }
    }

    /**
    * Handles the response returned from Guzzle client and converts the JSON string to array
    *
    * @param GuzzleResponse $response
    * @return array
    */
    private function handleResponse(GuzzleResponse $response)
    {
        return json_decode($response->getBody()->getContents());
    }

    /**
    * Handles the request headers into a single place
    *
    * @return array
    */
    private function requestHeadersArray()
    {
        return [
            'Authorization' => 'token ' . $this->personalAccessToken,
            'Accept'        => 'application/json',
            'Content-type'  => 'application/json'
        ];
    }
}