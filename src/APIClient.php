<?php

namespace Smartsheet;

use GuzzleHttp\Client as GuzzleClient;

/**
 * The Guzzle Client Wrapper
 */
class APIClient
{

    protected const BASE_URL = "https://api.smartsheet.com/2.0/";

    protected GuzzleClient $guzzleClient;

    /**
     * Undocumented function
     *
     * config
     *     token => A valid smartsheet API Token
     *
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $authHeader = "Bearer " . $config['token'];

        $this->guzzleClient = new GuzzleClient([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => $authHeader
            ]
        ]);
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
}
