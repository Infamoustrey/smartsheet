<?php

namespace Smartsheet\Resources;

use Exception;
use Smartsheet\SmartsheetClient;

const DEFAULT_COLUMNS = [
    [
        'title' => 'Primary',
        'type' => 'TEXT_NUMBER',
        'primary' => true,
    ],
];

class Workspace extends Resource
{
    protected SmartsheetClient $client;

    protected string $id;

    protected string $name;

    protected array $sheets = [];

    /**
     * Create a workspace resource.
     *
     * @param  SmartsheetClient  $client  The API client instance.
     * @param  mixed  $data  The raw workspace payload.
     */
    public function __construct(SmartsheetClient $client, $data)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     * Create a sheet inside the workspace.
     *
     * @param  mixed  $name  The sheet name.
     * @param  array[]  $columns
     * @return mixed
     */
    public function createSheet($name, $columns = DEFAULT_COLUMNS): mixed
    {
        return $this->client->post("workspaces/$this->id/sheets", [
            'json' => [
                'name' => $name,
                'columns' => $columns,
            ],
        ]);
    }

    /**
     * Fetches the sheet if it exists
     *
     * @param  string  $name  The sheet name.
     * @return Sheet
     *
     * @throws Exception
     */
    public function getSheet(string $name): Sheet
    {
        return $this->client->getSheet($this->getSheetId($name));
    }

    /**
     * Get the workspace sheets payload.
     *
     * @return array
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * Fetches the sheet if it exists
     *
     * @param  string  $name  The sheet name.
     * @return string
     *
     * @throws Exception
     */
    public function getSheetId(string $name): string
    {
        $sheet = collect($this->sheets)
            ->first(function ($sheet) use ($name) {
                return $sheet->name === $name;
            });

        if (is_null($sheet)) {
            throw new Exception('Sheet does not exist.');
        }

        return $sheet->id;
    }

    /**
     * Get the workspace identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the workspace name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
