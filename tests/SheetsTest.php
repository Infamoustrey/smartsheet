<?php

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
    $dotenv->load();
} catch (Exception $exception) {
    print('Attempting to get config from ENV');
}

use PHPUnit\Framework\TestCase;
use Smartsheet\SmartsheetClient;
use Smartsheet\Resources\Sheet;
use Smartsheet\Resources\Row;

class SheetsTest extends TestCase
{

    protected function getClient(): SmartsheetClient
    {
        return new SmartsheetClient(['token' => getenv('SMARTSHEET_API_TOKEN')]);
    }

    public function testCanFetchSheets(): void
    {
        $sheetList = $this->getClient()->listSheets();
        print('sheet count' . $sheetList->count());
        $this->assertGreaterThan(0, $sheetList->count(), 'No Sheets were found.');
    }

    public function testCanFetchSheet(): void
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->getId());

        $this->assertInstanceOf(Sheet::class, $sheet, 'Could not fetch sheet.');
    }

    public function testCanListColumns(): void
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->getId());

        $columns = $sheet->getColumns();

        $this->assertNotEmpty($columns, 'Unable to get sheet columns');
    }

    public function testCanInsertRows(): void
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->getId());

        $columns = $sheet->getColumns();

        $row = [];

        foreach ($columns as $column) {
            $row[$column->title] = 'testValue';
        }

        $response = $sheet->addRow($row);

        $this->assertEquals("SUCCESS", $response->message);
    }

    /**
     * @throws Exception
     */
    public function testCanLinkRow(): void
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->getId());

        $columns = $sheet->getColumns();

        $row = [];

        foreach ($columns as $column) {
            $row[$column->title] = 'testValue';
        }

        $response = $sheet->addRow($row);

        $this->assertNotNull($response, 'Unable to create row.');

        $row = $this->getClient()->getRow($sheet->getId(), $response->result->id);

        $response = $row->addAttachmentLink([
            'attachmentType' => 'LINK',
            'description' => 'Test Attachment',
            'name' => 'link',
            'url' => 'https://github.com/Infamoustrey/smartsheet'
        ]);

        $this->assertEquals("SUCCESS", $response->message, 'Unable to create row.');
    }

    /**
     *
     */
    public function testCanDeleteRow()
    {
        $sheets = $this->getClient()->listSheets();

        $sheet = $this->getClient()->getSheet($sheets[0]->getId());

        $response = $sheet->deleteRow($sheet->getRows()[0]->getId());

        $this->assertEquals("SUCCESS", $response->message);
    }
}
