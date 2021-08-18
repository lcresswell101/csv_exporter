<?php

namespace App\Services;

use App\Constants\CsvConstants;
use App\Models\Csv;

class CsvService
{
    /**
     * @param $csv
     * @return array
     */
    public function parseCsv($csv): array
    {
        return array_merge(...array_map('array_filter',
            array_map('str_getcsv',
                file($csv , FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            )
        ));
    }

    /**
     * @param array $csv
     */
    public function convertCsvToIndividualPeople(array $csv): void
    {
        foreach ($csv as $key => $row) {
           if(!$this->isColumnTitle($row)) {
               if ($this->checkContainsJoins($row)) {
                   $this->createWithTitle(
                       $this->getPeople(
                           $this->replaceJoins($row)
                       )
                   );
               } else {
                   if ($initial = $this->getInitial($row)) {
                       $this->createWithInitial(
                           $this->replaceInitial($row, $initial), $initial
                       );
                   } else {
                       $this->createWithoutJoinOrInitial($row);
                   }
               }
           }
        }
    }

    /**
     * @param string $row
     * @return mixed
     */
    public function isColumnTitle(string $row) {
      return in_array($row, CsvConstants::CSV_COLUMN_TITLES);
    }

    /**
     * @param array $row
     * @return bool
     */
    public function checkContainsJoins(string $row): bool
    {
        $count = 0;

        str_replace(CsvConstants::CSV_JOINS, '', $row, $count);

        return $count > 0;
    }

    /**
     * @param string $row
     * @return string|string[]
     */
    public function replaceJoins(string $row) {
        return str_replace(CsvConstants::CSV_JOINS, '', $row, $count);
    }

    /**
     * @param string $row
     * @return false|mixed|string|string[]
     */
    public function getInitial(string $row)
    {
        $nameSplit = explode(' ', $row);

        foreach ($nameSplit as $name) {
            $name = str_replace('.', '', $name);

            if(strlen($name) < 2) {
                return $name;
            }
        }

       return false;
    }

    /**
     * @param string $row
     * @param string $initial
     * @return string|string[]|null
     */
    public function replaceInitial(string $row, string $initial)
    {
        $removeFullStop = str_replace('.', '', $row);

        return preg_replace("/ {$initial} /", ' ', $removeFullStop, 1);
    }

    /**
     * @param string $row
     * @return array|false|string[]
     */
    public function getPeople(string $row)
    {
        $people = [];

        foreach (CsvConstants::CSV_TITLES as $title) {
            if(strpos($row, $title) !== false) {
                $titles[] = $title;

                $people = array_filter(explode($title, $row));
                $row = str_replace($title, '', $row);
            }
        }

        return array_map('trim', $people);
    }

    /**
     * @param array $people
     */
    public function createWithTitle(array $people): void
    {
        $titles = [];

        foreach ($people as $person) {
            foreach ($titles as $title) {
                $personSplit = explode(' ', $person);

                if (count($personSplit) > 1) {
                    Csv::create([
                        'title' => $title,
                        'first_name' => $personSplit[0],
                        'last_name' => $personSplit[1],
                    ]);
                } else {
                    Csv::create([
                        'title' => $title,
                        'last_name' => $personSplit[0],
                    ]);
                }
            }
        }
    }

    /**
     * @param string $row
     * @param string $initial
     */
    public function createWithInitial(string $row, string $initial): void
    {
        $nameSplit = explode(' ', $row);

        Csv::create([
            'title' => $nameSplit[0],
            'initial' => $initial,
            'last_name' => $nameSplit[1],
        ]);
    }

    /**
     * @param $row
     */
    public function createWithoutJoinOrInitial($row): void
    {
        $nameSplit = explode(' ', $row);

        Csv::create([
            'title' => $nameSplit[0],
            'first_name' => $nameSplit[1],
            'last_name' => $nameSplit[2],
        ]);
    }
}
