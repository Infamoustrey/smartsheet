<?php

namespace Smartsheet;

class Client
{

    protected const BASE_URL = "https://api.smartsheet.com/2.0/";

    protected $guzzleClient;

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

        $this->guzzleClient = new \GuzzleHttp\Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => $authHeader
            ]
        ]);
    }

    public function get(string $uri, array $options = [])
    {
        return $this->guzzleClient->get($uri, $options);
    }
}
