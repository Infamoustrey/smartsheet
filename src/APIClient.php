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
     * config
     *     token => A valid smartsheet API Token
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

    public function get(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->get($uri, $options)->getBody()->getContents());
    }

    public function put(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->put($uri, $options)->getBody()->getContents());
    }

    public function post(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->post($uri, $options)->getBody()->getContents());
    }

    public function delete(string $uri, array $options = [])
    {
        return json_decode($this->guzzleClient->delete($uri, $options)->getBody()->getContents());
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
