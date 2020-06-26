<?php

namespace Smartsheet;

class Result
{
    protected array $data;

    /**
     * Result constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->data = (array)$data;

        foreach ($this->data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get the data from the result
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get a specific property from the result
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key;
    }

    /**
     * Encodes the data into a json data
     * @return false|string
     */
    public function toJSON()
    {
        return json_encode($this->data);
    }
}
