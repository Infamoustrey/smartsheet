<?php

namespace Smartsheet;

class Contact extends Result
{
    protected Client $client;

    protected string $id, $name, $email;

    /**
     * Contact constructor.
     * @param $data
     * @param Client $client
     */
    public function __construct($data, Client $client)
    {
        parent::__construct($data);

        $this->client = $client;
    }
}
