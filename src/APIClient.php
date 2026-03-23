<?php

namespace Smartsheet;

use GuzzleHttp\Client as GuzzleClient;

/**
 * The Guzzle Client Wrapper
 */
class APIClient
{
    protected const BASE_URL = 'https://api.smartsheet.com/2.0/';

    protected GuzzleClient $guzzleClient;

    protected string $token;

    /**
     * Configure API Client
     *
     * @param  array  $config  Client configuration including the Smartsheet API token.
     */
    public function __construct(array $config)
    {
        $this->token = $config['token'];

        $authHeader = 'Bearer '.$this->token;

        $clientConfig = [
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => $authHeader,
            ],
        ];

        if (! empty($config['proxy'])) {
            $clientConfig['proxy'] = $config['proxy'];
        }

        if (! empty($config['handler'])) {
            $clientConfig['handler'] = $config['handler'];
        }

        $this->guzzleClient = $config['http_client'] ?? new GuzzleClient($clientConfig);
    }

    /**
     * Send a GET request to the Smartsheet API.
     *
     * @param  string  $uri  The relative API URI.
     * @param  array  $options  Guzzle request options.
     * @return object|null
     */
    public function get(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->get($uri, $options)->getBody()->getContents());
    }

    /**
     * Send a PUT request to the Smartsheet API.
     *
     * @param  string  $uri  The relative API URI.
     * @param  array  $options  Guzzle request options.
     * @return object|null
     */
    public function put(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->put($uri, $options)->getBody()->getContents());
    }

    /**
     * Send a POST request to the Smartsheet API.
     *
     * @param  string  $uri  The relative API URI.
     * @param  array  $options  Guzzle request options.
     * @return object|null
     */
    public function post(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->post($uri, $options)->getBody()->getContents());
    }

    /**
     * Send a DELETE request to the Smartsheet API.
     *
     * @param  string  $uri  The relative API URI.
     * @param  array  $options  Guzzle request options.
     * @return object|null
     */
    public function delete(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->delete($uri, $options)->getBody()->getContents());
    }

    /**
     * Get the configured API token.
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
