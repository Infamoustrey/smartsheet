<?php

use Smartsheet\Resources\Sheet;

class SheetsTest extends TestCase
{

    public function testCanFetchFolder(): void
    {
        $history = [];
        $folder = $this->getClient([
            $this->folderPayload(),
        ], $history)->getFolder('folder-123');

        $this->assertNotNull($folder);
        $this->assertSame('folder-123', $folder->getId());
        $this->assertSame('Mock Folder', $folder->get('name'));
        $this->assertRequest($history, 0, 'GET', 'folders/folder-123');
    }

    public function testCanFetchSheets(): void
    {
        $history = [];
        $sheetList = $this->getClient([
            [
                'data' => [
                    ['id' => 'sheet-123', 'name' => 'Mock Sheet'],
                    ['id' => 'sheet-456', 'name' => 'Backup Sheet'],
                ],
            ],
        ], $history)->listSheets();

        $this->assertCount(2, $sheetList);
        $this->assertSame('sheet-123', $sheetList[0]->getId());
        $this->assertRequest($history, 0, 'GET', 'sheets');
    }

    public function testCanFetchSheet(): void
    {
        $history = [];
        $sheet = $this->getClient([
            $this->sheetPayload(),
        ], $history)->getSheet('sheet-123');

        $this->assertInstanceOf(Sheet::class, $sheet, 'Could not fetch sheet.');
        $this->assertSame('sheet-123', $sheet->getId());
        $this->assertRequest($history, 0, 'GET', 'sheets/sheet-123');
    }

    public function testCanListColumns(): void
    {
        $history = [];
        $sheet = $this->getClient([
            $this->sheetPayload(),
        ], $history)->getSheet('sheet-123');

        $columns = $sheet->getColumns();

        $this->assertNotEmpty($columns, 'Unable to get sheet columns');
        $this->assertRequest($history, 0, 'GET', 'sheets/sheet-123');
    }

    /**
     * @throws Exception
     */
    public function testCanInsertRows(): void
    {
        $history = [];
        $sheet = $this->getClient([
            $this->sheetPayload(),
            $this->successPayload(['id' => 'row-999']),
        ], $history)->getSheet('sheet-123');

        $columns = $sheet->getColumns();

        $row = [];

        foreach ($columns as $column) {
            $row[$column->title] = 'testValue';
        }

        $response = $sheet->addRow($row);

        $this->assertEquals("SUCCESS", $response->message);
        $this->assertRequest($history, 0, 'GET', 'sheets/sheet-123');
        $this->assertRequest($history, 1, 'POST', 'sheets/sheet-123/rows');
    }

    /**
     * @throws Exception
     */
    public function testCanLinkRow(): void
    {
        $history = [];
        $client = $this->getClient([
            $this->sheetPayload(),
            $this->successPayload(['id' => 'row-999']),
            $this->rowPayload('row-999'),
            $this->sheetPayload(),
            $this->successPayload(['id' => 'attachment-123']),
        ], $history);

        $sheet = $client->getSheet('sheet-123');

        $columns = $sheet->getColumns();

        $row = [];

        foreach ($columns as $column) {
            $row[$column->title] = 'testValue';
        }

        $response = $sheet->addRow($row);

        $this->assertNotNull($response, 'Unable to create row.');

        $row = $client->getRow($sheet->getId(), $response->result->id);

        $response = $row->addAttachmentLink([
            'attachmentType' => 'LINK',
            'description' => 'Test Attachment',
            'name' => 'link',
            'url' => 'https://github.com/Infamoustrey/smartsheet'
        ]);

        $this->assertEquals("SUCCESS", $response->message, 'Unable to create row.');
        $this->assertRequest($history, 0, 'GET', 'sheets/sheet-123');
        $this->assertRequest($history, 1, 'POST', 'sheets/sheet-123/rows');
        $this->assertRequest($history, 2, 'GET', 'sheets/sheet-123/rows/row-999');
        $this->assertRequest($history, 3, 'GET', 'sheets/sheet-123');
        $this->assertRequest($history, 4, 'POST', 'sheets/sheet-123/rows/row-999/attachments');
    }

    /**
     *
     */
    public function testCanDeleteRow(): void
    {
        $history = [];
        $client = $this->getClient([
            [
                'data' => [
                    ['id' => 'workspace-123', 'name' => 'Mock Workspace'],
                ],
            ],
            $this->successPayload(['id' => 'sheet-123', 'name' => 'Disposable Sheet']),
            $this->sheetPayload('sheet-123', []),
            $this->successPayload(['id' => 'row-999']),
            $this->successPayload(),
        ], $history);

        $workspace = $client->listWorkspaces()->first();

        $this->assertNotNull($workspace, 'No workspace was found to create a test sheet in.');

        $createdSheet = $workspace->createSheet('Disposable Sheet');
        $sheet = $client->getSheet((string) $createdSheet->result->id);

        $rowResponse = $sheet->addRow([
            'Primary' => 'row-to-delete',
        ]);

        $this->assertEquals("SUCCESS", $rowResponse->message, 'Unable to create a test row.');

        $response = $sheet->deleteRow((string) $rowResponse->result->id);

        $this->assertEquals("SUCCESS", $response->message);
        $this->assertRequest($history, 0, 'GET', 'workspaces');
        $this->assertRequest($history, 1, 'POST', 'workspaces/workspace-123/sheets');
        $this->assertRequest($history, 2, 'GET', 'sheets/sheet-123');
        $this->assertRequest($history, 3, 'POST', 'sheets/sheet-123/rows');
        $this->assertRequest($history, 4, 'DELETE', 'sheets/sheet-123/rows', 'ids=row-999');
    }

    private function folderPayload(): array
    {
        return [
            'id' => 'folder-123',
            'name' => 'Mock Folder',
            'permaLink' => 'https://example.test/folders/folder-123',
            'sheets' => [],
        ];
    }

    private function sheetPayload(string $sheetId = 'sheet-123', ?array $rows = null): array
    {
        return [
            'id' => $sheetId,
            'name' => 'Mock Sheet',
            'version' => '1',
            'hasSummaryFields' => false,
            'permalink' => 'https://example.test/sheets/' . $sheetId,
            'createdAt' => '2026-03-23T00:00:00Z',
            'modifiedAt' => '2026-03-23T00:00:00Z',
            'isMultiPickListEnabled' => false,
            'columns' => [
                [
                    'id' => 'col-1',
                    'title' => 'Primary',
                    'type' => 'TEXT_NUMBER',
                    'primary' => true,
                ],
                [
                    'id' => 'col-2',
                    'title' => 'Status',
                    'type' => 'TEXT_NUMBER',
                ],
            ],
            'rows' => $rows ?? [
                [
                    'id' => 'row-123',
                    'sheetId' => $sheetId,
                    'rowNumber' => 1,
                    'cells' => [
                        [
                            'columnId' => 'col-1',
                            'value' => 'existing-row',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function rowPayload(string $rowId, string $sheetId = 'sheet-123'): array
    {
        return [
            'id' => $rowId,
            'sheetId' => $sheetId,
            'rowNumber' => 2,
            'cells' => [
                [
                    'columnId' => 'col-1',
                    'value' => 'testValue',
                ],
            ],
        ];
    }

    private function successPayload(array $result = []): array
    {
        return [
            'message' => 'SUCCESS',
            'result' => $result,
        ];
    }
}
