<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelTemplateService
{
    /**
     * Generates an Excel file based on the selected bank template and provided data.
     *
     * @param array  $records   The data to populate in the Excel file
     * @param string $fileName  The name of the generated Excel file (with extension)
     * @param string $bank      The bank identifier ('KVB' or 'HDFC') to choose the appropriate template
     * @param string $date      The date to be placed in the template (for KVB)
     *
     * @return string  The full file path to the generated Excel file
     */
    public function generate(array $records, string $fileName, string $bank, string $date): string
    {
        if($bank == "KVB")
            $templatePath = storage_path('app/templates/kvb.xlsx');
        else if($bank == "HDFC")
            $templatePath = storage_path('app/templates/hdfc.xlsx');

        $exportPath = storage_path('app/public/' . $fileName);

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        if($bank === "KVB")
            $this->formatSheetKVB($sheet, $records, $date);
        else if($bank === "HDFC")
            $this->formatSheetHDFC($sheet, $records);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($exportPath);

        return $exportPath;
    }

    /**
     * Populates and formats the KVB Excel sheet with given records and date.
     *
     * @param Worksheet $sheet   The active sheet to populate
     * @param array     $records The data records to insert
     * @param string    $date    The transaction date
     *
     * @return void
     */
    private function formatSheetKVB(Worksheet $sheet, array $records, string $date): void
    {
        $sheet->setCellValue('D2', $date);
        $rowIndex = 6; // start row

        foreach ($records as $record) {
            $colIndex = 1; // Column A = 1
            foreach ($record as $cellValue) {
                $cell = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                if ($colIndex === 2 || $colIndex === 4) // Column B and D
                    $sheet->setCellValueExplicit($cell, (string) $cellValue, DataType::TYPE_STRING);
                else
                    $sheet->setCellValue($cell, $cellValue);

                $colIndex++;
            }
            $rowIndex++;
        }

        foreach (['E', 'F', 'G', 'H', 'I', 'L'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Populates and formats the HDFC Excel sheet with given records.
     *
     * @param Worksheet $sheet   The active sheet to populate
     * @param array     $records The data records to insert
     *
     * @return void
     */
    private function formatSheetHDFC(Worksheet $sheet, array $records): void
    {
        $rowIndex = 2; // start row

        foreach ($records as $record) {
            $colIndex = 1; // Column A = 1
            foreach ($record as $cellValue) {
                $cell = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                if ($colIndex === 3) // Column C = 3
                    $sheet->setCellValueExplicit($cell, (string) $cellValue, DataType::TYPE_STRING);
                else
                    $sheet->setCellValue($cell, $cellValue);

                $colIndex++;
            }
            $rowIndex++;
        }
        
        foreach (['E', 'O', 'Z', 'AA', 'AB'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Prepares and returns a download response for the given Excel file.
     *
     * Clears the output buffer to prevent file corruption,
     * and optionally deletes the file after it is sent to the browser.
     *
     * @param string $filePath     File path to the Excel file
     * @param bool   $deleteAfter  Whether to delete the file after sending (default: true)
     * @return BinaryFileResponse
     */
    public function download(string $filePath, bool $deleteAfter = true): BinaryFileResponse
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()
            ->download($filePath)
            ->deleteFileAfterSend($deleteAfter);
    }
}