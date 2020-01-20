<?php

namespace Smartsheet;

class Sheets
{

    protected $client;

    public function __construct(Client $client = null)
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
