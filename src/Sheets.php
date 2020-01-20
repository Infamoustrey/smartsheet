<?php

namespace Smartsheet;

class Sheet
{

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function fetch()
    {
        $response = $this->client->get('sheets');

        $sheets = json_decode($response->getBody())->data;

        return $sheets;
    }
}
