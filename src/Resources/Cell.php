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

    public function __construct(SmartsheetClient $client, array $data)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     * Returns either the formula or the value depending on if the cell uses a formula
     * @return string
     */
    public function getValue()
    {
        return $this->value ?? '';
    }

    /**
     * @return string
     */
    public function getColumnId()
    {
        return $this->columnId;
    }
}
