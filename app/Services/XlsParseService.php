<?php

namespace App\Services;

use App\Contracts\Services\XlsParseServiceInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class XlsParseService implements XlsParseServiceInterface
{

    /**
     * Получить массив с данными из XLS файла
     *
     * @param UploadedFile $file
     * @param int $maxRowsCount
     * @param int $maxColumnsCount
     * @param int $headerRowsCount
     * @return array
     * @throws Exception
     */
    public function parseOrderSenderXlsToArray(
        UploadedFile $file,
        int $maxRowsCount = 0,
        int $maxColumnsCount = 0,
        int $headerRowsCount = 0
    ): array {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();

        $this->checkRowsCount($worksheet, $maxRowsCount, $headerRowsCount);
        $this->checkColumnsCount($worksheet, $maxColumnsCount);

        $sheetDataWithHeaders = $worksheet->toArray();
        return array_slice($sheetDataWithHeaders, $headerRowsCount);
    }

    /**
     * @throws Exception
     */
    private function checkRowsCount(Worksheet $worksheet, int $maxRowsCount, int $headerRowsCount): void
    {
        if ($maxRowsCount == 0) {
            return;
        }

        $filledRows = $worksheet->getHighestRow() - $headerRowsCount;
        if ($filledRows > $maxRowsCount) {
            throw new Exception('В файле более ' . $maxRowsCount . ' строк');
        }
        if ($filledRows < 1) {
            throw new Exception('В файле нет строк для обработки');
        }
    }

    /**
     * @throws Exception
     */
    private function checkColumnsCount(Worksheet $worksheet, int $maxColumnsCount): void
    {
        if ($maxColumnsCount == 0) {
            return;
        }

        $columnCount = $worksheet->getHighestColumn();
        $columnCount = Coordinate::columnIndexFromString($columnCount);
        if ($columnCount > $maxColumnsCount) {
            throw new Exception('В файле более ' . $maxColumnsCount .  ' столбцов');
        }
    }

}
