<?php

namespace Unit;

use App\Services\CsvService;
use App\Constants\CsvConstants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvServiceTest extends TestCase
{
    protected CsvService $csvService;

    /**
     * @return array[]
     */
    public function columnTitleProvider(): array
    {
        return [
            [
                'homeowner',
                true,
            ],
            [
                'Mr F. Fredrickson',
                false,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function containsJoinsProvider(): array
    {
        return [
            [
                'Mr Tom Staff and Mr John Doe',
                true,
            ],
            [
                'Mr F. Fredrickson',
                false,
            ],
        ];
    }

    /**
     * @return string[][]
     */
    public function replacesJoinsProvider(): array
    {
        return [
            [
                'Mr Tom Staff and Mr John Doe',
                'and',
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function containsInitialProvider(): array
    {
        return [
            [
                'Mr F. Fredrickson',
                true,
            ],
            [
                'Mrs Jane McMaster',
                false,
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function peopleProvider(): array
    {
        return [
            [
                'Mr Tom Staff and Mr John Doe',
                [
                    'Tom Staff',
                    'John Doe',
                ],
            ],
            [
                'Mr and Mrs Smith',
                [
                    'Smith',
                ],
            ]
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        $this->csvService = app(CsvService::class);
    }

    public function testParseCsvReturnsArray() {
        $csv = UploadedFile::fake()->create("{$this->faker->word}.csv");

        $this->assertSame('array', gettype($this->csvService->parseCsv($csv)));
    }

    /**
     * @dataProvider columnTitleProvider
     * @param string $row
     * @param bool $isColumnTitle
     */
    public function testIsColumnTitles(string $row, bool $isColumnTitle) {
        $this->assertSame($isColumnTitle, $this->csvService->isColumnTitle($row));
    }

    /**
     * @dataProvider containsJoinsProvider
     * @param string $row
     * @param bool $containsJoins
     */
    public function testCheckContainsJoins(string $row, bool $containsJoins) {
        $this->assertSame($containsJoins, $this->csvService->checkContainsJoins($row));
    }

    /**
     * @dataProvider replacesJoinsProvider
     * @param string $row
     * @param string $join
     */
    public function testReplaceJoins(string $row, string $join)
    {
        $this->assertFalse(strpos($this->csvService->replaceJoins($row), $join));
    }

    /**
     * @dataProvider containsInitialProvider
     * @param string $row
     * @param bool $containsInitial
     */
    public function testGetInitialReturnsCorrectResult(string $row, bool $containsInitial) {
        if ($containsInitial) {
            $this->assertNotFalse($this->csvService->getInitial($row));
        } else {
            $this->assertFalse($this->csvService->getInitial($row));
        }
    }

    /**
     * @dataProvider peopleProvider
     * @param string $row
     * @param array $people
     */
    public function testGetPeopleReturnsCorrectResult(string $row, array $people) {

        $this->assertEqualsCanonicalizing($people, $this->csvService->getPeople(
            $this->csvService->replaceJoins($row)
        ));
    }

    public function testSplitCsvCanHandleTwoNamesWithJoin() {
        $csv = [
            'Mr Tom Staff and Mr John Doe',
        ];

        $this->csvService->convertCsvToIndividualPeople($csv);

        $this->assertDatabaseHas('csvs', [
           'title' => 'Mr',
           'first_name' => 'Tom',
           'last_name' => 'Staff',
        ]);

        $this->assertDatabaseHas('csvs', [
            'title' => 'Mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function testSplitCsvCanHandleOneNameWithJoin() {
        $csv = [
            'Mr and Mrs Smith',
        ];

        $this->csvService->convertCsvToIndividualPeople($csv);

        $this->assertDatabaseHas('csvs', [
            'title' => 'Mr',
            'last_name' => 'Smith',
        ]);

        $this->assertDatabaseHas('csvs', [
            'title' => 'Mrs',
            'last_name' => 'Smith',
        ]);
    }

    public function testSplitCsvCanHandleOneNameWithInitial() {
        $csv = [
            'Mr F. Fredrickson',
        ];

        $this->csvService->convertCsvToIndividualPeople($csv);

        $this->assertDatabaseHas('csvs', [
            'title' => 'Mr',
            'initial' => 'F',
            'last_name' => 'Fredrickson',
        ]);
    }

    public function testSplitCsvCanHandleOneNameWithInitialWithoutFullStop() {
        $csv = [
            'Mr M Mackie',
        ];

        $this->csvService->convertCsvToIndividualPeople($csv);

        $this->assertDatabaseHas('csvs', [
            'title' => 'Mr',
            'initial' => 'M',
            'last_name' => 'Mackie',
        ]);
    }

    public function testSplitCsvCanHandleOneName() {
        $csv = [
            'Mrs Jane McMaster',
        ];

        $this->csvService->convertCsvToIndividualPeople($csv);

        $this->assertDatabaseHas('csvs', [
            'title' => 'Mrs',
            'first_name' => 'Jane',
            'last_name' => 'McMaster',
        ]);
    }
}
