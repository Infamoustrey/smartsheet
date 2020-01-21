<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

use PHPUnit\Framework\TestCase;
use Smartsheet\Client;
use Smartsheet\Sheets;

class SheetsTest extends TestCase
{

    private function getClient()
    {
        return new Client(['token' => getenv('SMARTSHEET_API_TOKEN')]);
    }

    public function testCanFetchSheets(): void
    {
        $sheets = new Sheets($this->getClient());

        $sheetList = $sheets->list();

        $this->assertNotEmpty($sheetList);
    }

    public function testCanListColumns(): void
    {
        $sheets = new Sheets($this->getClient());
        $sheetList = $sheets->list();

        $sheet = $sheets->fetch($sheetList[0]->id);

        $columns = $sheet->columns;

        $this->assertNotEmpty($columns);
    }

    public function testCanInsertRows(): void
    {
        $sheets = new Sheets($this->getClient());
        $sheetList = $sheets->list();

        $sheet = $sheets->fetch($sheetList[0]->id);

        $columns = $sheet->columns;

        $row = [
            "cells" => []
        ];

        foreach ($columns as $column) {
            $row['cells'][] = [
                'columnId' => $column->id,
                'value' => 'testValue'
            ];
        }

        $response = $sheets->insertRow($sheet->id, [$row]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanDeleteRow()
    {
        $sheets = new Sheets($this->getClient());
        $sheetList = $sheets->list();

        $sheet = $sheets->fetch($sheetList[0]->id);

        $response = $sheets->deleteRow($sheet->id, $sheet->rows[0]->id);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
