<?php

namespace Smartsheet;

class Cell extends Result
{
    protected Client $client;

    protected string $columnId;
    protected string $value;
    protected string $displayValue;
    protected string $formula;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->client = resolve(SmartsheetClient::class);
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
     * Returns the column id for the cell
     * @return string
     */
    public function getColumnId()
    {
        return $this->columnId;
    }

}
