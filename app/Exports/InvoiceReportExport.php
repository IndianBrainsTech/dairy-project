<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\ReportController;
use App\Models\Places\MRoute;

class InvoiceReportExport implements FromView, WithTitle, WithProperties, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected $fromDate, $toDate, $routeId, $heading, $reportData, $titles, $data;

    public function __construct($fromDate, $toDate, $routeId, $heading)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->routeId = $routeId;
        $this->heading = $heading;

        // Fetch the report data in the constructor
        $routes = MRoute::select('id', 'name')->orderBy('name')->get();
        $report = new ReportController();
        $this->reportData = $report->getInvoiceData($this->fromDate, $this->toDate, $this->routeId, $routes);

        // Define titles and data
        $this->titles = ['S.No', 'Date', 'Customer', 'Invoice Number', 'Qty', 'Amount']; // Adjust as needed
        $this->data = $this->reportData['data']; // Ensure this contains the correct data
    }

    public function title(): string
    {
        return 'Invoice Report';
    }

    public function properties(): array
    {
        return [
            'creator'        => 'Aasaii',
            'title'          => 'Invoice Report',
            'description'    => $this->heading,
            'company'        => 'Aasaii Food Product',
        ];
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function view(): View
    {
        return view('reports.export.invoice_report_export', [
            'fromDate'       => $this->fromDate,
            'toDate'         => $this->toDate,
            'routeId'        => $this->routeId,
            'reportData'     => $this->reportData['data'],
            'grandTotals'    => $this->reportData['totals'],
            'routeRecords'   => $this->reportData['routeRecords'],
            'payModeRecords' => $this->reportData['payModeRecords'],
            'heading'        => $this->heading,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $cc = count($this->titles); // Column Count
                $lr = $event->sheet->getDelegate()->getHighestRow();
                $lc = Coordinate::stringFromColumnIndex($cc); // Last Column

                // ===== All Data Styling =====
                $allData = 'A1:'.$lc.$lr;
                $sheet->getStyle($allData)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($allData)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // ===== Row 1 (Main Title) =====
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->setCellValue('A1', "AASAII FOOD PRODUCT");
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

                // ===== Row 2 (Sub Title / Heading) =====
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->setCellValue('A2', $this->heading);
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

                // ===== Heading Row (Titles) =====
                $sheet->getRowDimension(3)->setRowHeight(20);
                $headingRow = 'A3:'.$lc.'3';
                $sheet->getStyle($headingRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
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
                $sheet->getStyle($headingRow)->applyFromArray($styleArray);

                // ===== Data Rows Formatting =====
                $dataCells = 'A4:'.$lc.$lr;
                $sheet->getStyle($dataCells)->getAlignment()->setIndent(1);
                $sheet->freezePane('A4');

                // ===== Ensure Last Row Formatting =====
                $sheet->getStyle("A{$lr}:C{$lr}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A'.$lr.':'.$lc.$lr)->applyFromArray($styleArray);


                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // A Center
                // $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // B Center
                $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);   // C Left
                $sheet->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // D Center
                $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // D Center
                $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // D Center

                // E and F as 0.00 format
                $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode('#,##0.00'); 
                $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('#,##0.00'); 

                // Remaining Rows Height    
                for($r=3; $r<=$lr; $r++)
                    $sheet->getRowDimension($r)->setRowHeight(20);

    
            }
        ];
    }

}