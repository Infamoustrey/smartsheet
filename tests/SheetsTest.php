<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

use PHPUnit\Framework\TestCase;
use Smartsheet\Client;
use Smartsheet\Sheet;
use Smartsheet\Row;

class SheetsTest extends TestCase
{

    private function getClient()
    {
        return new Client(['token' => getenv('SMARTSHEET_API_TOKEN')]);
    }

    public function testCanFetchSheets(): void
    {

        $sheetList = $this->getClient()->listSheets();

        $this->assertNotEmpty($sheetList);
    }

    public function testCanListColumns(): void
    {

        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->id);

        $columns = $sheet->getColumns();

        $this->assertNotEmpty($columns);
    }

    public function testCanInsertRows(): void
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->id);

        $columns = $sheet->getColumns();

        $row = [];

        foreach ($columns as $column) {
            $row[$column->title] = 'testValue';
        }

        $response = $sheet->addRow($row);

        $this->assertEquals("SUCCESS", $response->message);
    }

    public function testCanLinkRow(): void
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->id);

        $columns = $sheet->getColumns();

        $row = [];

        foreach ($columns as $column) {
            $row[$column->title] = 'testValue';
        }

        $response = $sheet->addRow($row);

        $row = $this->getClient()->getRow($sheet->getId(), $response->result->id);

        $response = $row->addAttachmentLink([
            'attachmentType' => 'LINK',
            'description' => 'Test Attachment',
            'name' => 'link',
            'url' => 'https://github.com/Infamoustrey/smartsheet'
        ]);

        $this->assertEquals("SUCCESS", $response->message);
    }

    public function testCanDeleteRow()
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->id);

        $response = $sheet->deleteRow($sheet->getRows()[0]->getId());

        $this->assertEquals("SUCCESS", $response->message);
    }
}
