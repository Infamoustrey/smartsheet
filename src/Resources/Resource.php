<?php

namespace Smartsheet\Resources;

class Resource
{
    protected array $data;

    /**
     * Create a resource from a raw API payload.
     *
     * @param  array  $data  The raw resource payload.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;

        foreach ($this->data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get the full raw resource payload.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get a single value from the raw resource payload.
     *
     * @param  string|int  $key  The payload key to retrieve.
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Encode the raw payload as JSON.
     */
    public function toJSON(): string
    {
        return json_encode($this->data);
    }
}
