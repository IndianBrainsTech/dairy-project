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
use PhpOffice\PhpSpreadsheet\Cell\IgnoreError;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HSNwiseSummaryExport implements FromArray, WithTitle, WithHeadings, WithProperties, WithEvents, WithCustomStartCell, ShouldAutoSize
{
    protected $heading;
    protected $titles;
    protected $type;
    protected $data;

    public function __construct(string $heading, array $titles, string $type, array $data)
    {
        $this->heading = $heading;
        $this->titles = $titles;
        $this->type = $type;
        $this->data = $data;
    }

    public function title(): string
    {
        return 'HSN Wise Sales Report';
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
            'title'          => 'HSN Wise Summary',
            'description'    =>  $this->heading,
            'company'        => 'Aasaii Food Productt',
        ];
    }

    public function startCell(): string
    {
        return 'A3';
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
                
                // All Data
                $allData = "A1:{$lc}{$lr}";
                $sheet->getStyle($allData)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($allData)    
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Row 1
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->setCellValue('A1',"AASAII FOOD PRODUCTT");
                $row1 = "A1:{$lc}1";
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
                $row2 = "A2:{$lc}2";
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
                $headingRow = "A3:{$lc}3";
                if($this->type == "Format1")
                    $sheet->getRowDimension(3)->setRowHeight(32);
                $sheet->getStyle($headingRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);;
                $sheet->getStyle($headingRow)->applyFromArray($styleArray);

                // Data Rows
                $dataCells = "A4:{$lc}{$lr}";
                $sheet->getStyle($dataCells)->getAlignment()->setIndent(1);

                // Freeze Pane
                $sheet->freezePane('D4');

                // Total Row                
                $totalRow = "A{$lr}:{$lc}{$lr}";
                $cl = ($this->type == "Format1") ? "E" : "C";
                $sheet->mergeCells("A{$lr}:{$cl}{$lr}");
                $sheet->getStyle("A{$lr}:B{$lr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($totalRow)->applyFromArray($styleArray);

                // Column Alignment
                $lr1 = $lr-1;
                $snoColumn = "A4:A{$lr1}";
                $sheet->getStyle($snoColumn)->getAlignment()->setIndent(0)->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $cl = ($this->type == "Format1") ? "B" : "C";
                $hsnColumn = "{$cl}4:{$cl}{$lr1}";
                $sheet->getStyle($hsnColumn)->getAlignment()->setIndent(0)->setHorizontal(Alignment::HORIZONTAL_CENTER);
                if($this->type == "Format1")
                    $sheet->getStyle("H4:H{$lr1}")->getAlignment()->setIndent(0)->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format Numbers
                $cl = ($this->type == "Format1") ? "F" : "D";
                $numCells = "{$cl}4:{$lc}{$lr}";
                $sheet->getStyle($numCells)->getNumberFormat()->setFormatCode('0.00');

                // Focus to Title
                $sheet->getStyle($row2)->getFont()->setBold(true);
            }
        ];
    }
}
