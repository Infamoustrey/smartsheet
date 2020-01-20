<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

use PHPUnit\Framework\TestCase;
use Smartsheet\Client;
use Smartsheet\Sheets;

final class SheetsTest extends TestCase
{

    public function testCanFetchSheets(): void
    {
        $client = new Client(['token' => getenv('SMARTSHEET_API_TOKEN')]);

        $sheets = new Sheets($client);

        $sheetList = $sheets->fetch();

        $this->assertNotEmpty($sheetList);
    }
}
