<?php

namespace Smartsheet\Resources;

use Smartsheet\SmartsheetClient;

class Cell extends Resource
{
    protected SmartsheetClient $client;

    protected string $columnId;

    protected string $value;

    protected string $displayValue;

    protected string $formula;

    /**
     * Create a cell resource.
     *
     * @param  SmartsheetClient  $client  The API client instance.
     * @param  array  $data  The raw cell payload.
     */
    public function __construct(SmartsheetClient $client, array $data)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     * Returns either the formula or the value depending on if the cell uses a formula
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value ?? '';
    }

    /**
     * Get the column identifier for the cell.
     */
    public function getColumnId(): string
    {
        return $this->columnId;
    }
}
