<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class statementStyleCustomerExport implements FromArray, WithTitle, WithHeadings, WithProperties, WithEvents, WithCustomStartCell, ShouldAutoSize, WithColumnFormatting
{
    protected $heading;
    protected $titles;
    protected $data;

    public function __construct(string $heading, array $titles, array $row)
    {
        $this->heading = $heading;
        $this->titles = $titles;
        $this->data = $row;
    }

    public function title(): string
    {
        return 'Customer Statement Report';
    }

    public function headings(): array
    {
        return $this->titles;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'Aasaii',
            'title'          => 'Customer Statement Report',
            'description'    =>  $this->heading,
            'company'        => 'Aasaii Food Productt',
        ];
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00, // Inv Tot Amt
            'D' => NumberFormat::FORMAT_NUMBER_00, // Inv Tot Amt
            'E' => NumberFormat::FORMAT_NUMBER_00, // Inv Tot Amt
            'F' => NumberFormat::FORMAT_NUMBER_00, // Cash
            'G' => NumberFormat::FORMAT_NUMBER_00, // Bank
            'H' => NumberFormat::FORMAT_NUMBER_00, // Incentive
            'I' => NumberFormat::FORMAT_NUMBER_00, // Deposit
            'J' => NumberFormat::FORMAT_NUMBER_00, // Returns
            'K' => NumberFormat::FORMAT_NUMBER_00, // Discount
            'L' => NumberFormat::FORMAT_NUMBER_00, // Balance
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                
                $sheet = $event->sheet->getDelegate();                
                $cc = count($this->titles); //columnCount
                $lr = count($this->data) + 3; // rowCount/lastRow
                $lc = Coordinate::stringFromColumnIndex($cc); // lastColumn

                // Right-align columns C and D
                $rightAlignedColumns = ['C', 'D','G'];
                foreach ($rightAlignedColumns as $column) {
                    $sheet->getStyle("{$column}4:{$column}{$lr}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                
                // All Data Table 1
                $allData = 'A1:'.$lc.$lr-7;
                $sheet->getStyle($allData)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($allData)    
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Row 1
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->setCellValue('A1',"AASAII FOOD PRODUCTT");
                $row1 = 'A1:'.$lc.'1';
                $sheet->mergeCells($row1);
                $sheet->getStyle($row1)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($row1)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle($row1)
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFEECFA');
                $sheet->getStyle($row1)
                    ->getFont()->setSize(15)->setBold(true)
                    ->getColor()->setARGB('FFFF0000');

                // Row 2
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->setCellValue('A2',$this->heading);
                $row2 = 'A2:'.$lc.'2';
                $sheet->mergeCells($row2);
                $sheet->getStyle($row2)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($row2)
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEBEFFF');
                $sheet->getStyle($row2)
                    ->getFont()->setSize(12)->setBold(true)
                    ->getColor()->setARGB('FF00008B');

                // Remaining Rows Height    
                for($r=3; $r<=$lr; $r++)
                    $sheet->getRowDimension($r)->setRowHeight(20);
                 
                // Heading Style
                $styleArray = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF1C205B']
                    ],
                    
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF3F3F3']
                    ],
                ];

                // Heading Row
                $headingRow = 'A3:'.$lc.'3';
                $sheet->getStyle($headingRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headingRow)->applyFromArray($styleArray);
                // $sheet->setAutoFilter($headingRow);

                // Data Rows
                $dataCells = 'A4:'.$lc.($lr);
                $sheet->getStyle($dataCells)->getAlignment()->setIndent(1);
                $sheet->freezePane('A4');

                $totalRow = $lr - 8; // 3 is the offset from starting at A3                
                $grandTotalRange = "A{$totalRow}:{$lc}{$totalRow}";
                $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                $sheet->getStyle("A{$totalRow}:B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($grandTotalRange)->applyFromArray($styleArray);
                //empty Row
                // Determine the row number for the empty row
                $emptyRow = $totalRow + 1;
                $emptyRowRange = "A{$emptyRow}:{$lc}{$emptyRow}"; // Merge all columns in the empty row             
                $sheet->mergeCells($emptyRowRange);               

                //summary Table                
                $summaryRow = $totalRow + 2; 
                $headingRow2 = "A{$summaryRow}:D{$summaryRow}"; 
                $sheet->mergeCells($headingRow2);           
                $sheet->getStyle($headingRow2)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
                $sheet->getStyle($headingRow2)
                    ->applyFromArray($styleArray); 

                $dataStart = $summaryRow + 1;
                $dataCells = 'A' . $dataStart . ':D' . $lr; 
                $sheet->getStyle($dataCells)->getAlignment()->setIndent(1); 
                // Merge columns for each summary row
                for ($i = $dataStart; $i <= $lr; $i++) {
                    $sheet->mergeCells("A{$i}:B{$i}");       
                    $sheet->mergeCells("C{$i}:D{$i}");
                }

                // Apply 0.00 format to B column in the summary table                
                $sheet->getStyle("B{$dataStart}:D{$lr}")
                    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                // All Data Table 2
                $allData2 = "A" . $summaryRow . ":D" . $lr;
                $sheet->getStyle($allData2)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($allData2)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        ];
    }
}
