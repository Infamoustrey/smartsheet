<?php

namespace Smartsheet;

use GuzzleHttp\Client;

/**
 * The Guzzle Client Wrapper
 */
class APIClient
{

    protected const BASE_URL = "https://api.smartsheet.com/2.0/";
    protected string $auth_token;

    protected Client $guzzleClient;

    /**
     * APIClient constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->auth_token = $config['token'];

        $authHeader = "Bearer " . $this->auth_token;

        $this->guzzleClient = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => $authHeader
            ]
        ]);
    }

    /**
     * Send a get request and get back a json response
     * @param string $uri
     * @param array $options
     * @return mixed
     */
    public function get(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->get($uri, $options)->getBody()->getContents());
    }

    /**
     * Send a put request and get back a json response
     * @param string $uri
     * @param array $options
     * @return mixed
     */
    public function put(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->put($uri, $options)->getBody()->getContents());
    }

    /**
     * Send a post request and get back a json response
     * @param string $uri
     * @param array $options
     * @return mixed
     */
    public function post(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->post($uri, $options)->getBody()->getContents());
    }

    /**
     * Send a delete request and get back a json response
     * @param string $uri
     * @param array $options
     * @return mixed
     */
    public function delete(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->delete($uri, $options)->getBody()->getContents());
    }

    /**
     * Returns the auth token
     * @return mixed|string
     */
    public function getAuthToken()
    {
        return $this->auth_token;
    }
}
