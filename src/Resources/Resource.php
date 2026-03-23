<?php

namespace Smartsheet\Resources;

class Resource
{
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;

        foreach ($this->data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function toJSON(): string
    {
        return json_encode($this->data);
    }
}
