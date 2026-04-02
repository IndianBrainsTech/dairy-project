<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profiles\Employee;
use App\Models\Transactions\Enquiry;
use App\Models\Transactions\Followup;
use App\Models\Transactions\Attendance;
use App\Models\Transactions\Receipt;
use App\Models\Transactions\ReceiptData;
use App\Models\Products\Product;
use App\Models\Products\ProductUnit;
use App\Models\Products\ProductGroup;
use App\Models\Products\UOM;
use App\Models\Places\Area;
use App\Models\Places\MRoute;
use App\Models\Profiles\Customer;
use App\Models\Masters\GstMaster;
use App\Models\Masters\BankMaster;
use App\Models\Masters\Outstanding;
use App\Models\Orders\Order;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\SalesInvoiceItem;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\TaxInvoiceItem;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Orders\BulkMilkOrderItem;
use App\Models\Orders\JobWork;
use App\Models\Orders\JobWorkItem;
use App\Models\Orders\SalesReturn;
use App\Models\Transactions\CreditNotes\CreditNote;
use App\Services\Reports\DayWiseReportService;
use App\Enums\DocumentStatus;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

/* Enquiry Report - Start */
    public function enquiryReport(Request $request)
    {
        $fromDate = $request->input('fromDate', date('Y-m-d'));
        $toDate = $request->input('toDate', date('Y-m-d'));
        $empId = $request->input('empId', 0);
        $areaId = $request->input('areaId', 0);

        $areas      = Area::select('id','name')->orderBy('name')->get();
        $employees  = Employee::select('id','name')->where('status','Active')->orderBy('name')->get();
        $enquiries  = Enquiry::select('id','shop_name','area_name','contact_num','enq_datetime','emp_id','conversion_status')
                        ->with('employee:id,name')
                        ->whereBetween('enq_datetime',[$fromDate." 00:00:00", $toDate." 23:59:59"])
                        ->when($empId<>0, function($query) use($empId) { return $query->where('emp_id', $empId); })
                        ->when($areaId<>0, function($query) use($areaId) { return $query->where('area_id', $areaId); })
                        ->orderBy('enq_datetime')
                        ->get();
                        
        foreach($enquiries as $enquiry) {
            $followups = Followup::where('enquiry_id',$enquiry->id)->get();
            $enquiry['followups'] = count($followups);
        }

        // return response()->json([
        return view('reports.enquiry-report', [
            'fromDate'  => $fromDate,
            'toDate'    => $toDate,
            'empId'     => $empId,
            'areaId'    => $areaId,
            'areas'     => $areas,
            'employees' => $employees,
            'enquiries' => $enquiries
        ]);
    }
/* Enquiry Report - End */


/* Attendance Report - Section Start */
    public function attendanceReport(Request $request)
    {
        $fromDate = $request->input('fromDate', date('Y-m-d'));
        $toDate = $request->input('toDate', date('Y-m-d'));
        $empId = $request->input('empId', 0);

        $employees = Employee::select('id','name','code')->where('status','Active')->orderBy('name')->get();
        $attendanceData = $this->getAttendanceData($fromDate, $toDate, $empId);

        // return response()->json([
        return view('reports.attendance-report', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'empId' => $empId,
            'employees' => $employees,
            'attendances' => $attendanceData
        ]);
    }
    
    public function getAttendanceData($fromDate, $toDate, $empId)
    {
        $attendanceRecords = Attendance::selectRaw('emp_id, attn_date, GROUP_CONCAT(attn_session) as attn_session, GROUP_CONCAT(time_in) as time_in, GROUP_CONCAT(time_out) as time_out')
                                        ->with('employee:id,name,code')
                                        ->whereBetween('attn_date', ["{$fromDate} 00:00:00", "{$toDate} 23:59:59"])
                                        ->when($empId != 0, fn($query) => $query->where('emp_id', $empId))
                                        ->orderBy('id')
                                        ->groupBy(['attn_date', 'emp_id'])
                                        ->get();
    
        $attendanceData = [];
        $sno = 1;
    
        foreach ($attendanceRecords as $attnData) {
            $sessions = explode(",", $attnData->attn_session);
            $timeIn = explode(",", $attnData->time_in);
            $timeOut = explode(",", $attnData->time_out);
    
            $tmin1 = $tmin2 = $tmout1 = $tmout2 = "";
            foreach ($sessions as $i => $session) {
                if ($session === "Forenoon") {
                    $tmin1 = $timeIn[$i] ?? "";
                    $tmout1 = $timeOut[$i] ?? "";
                } elseif ($session === "Afternoon") {
                    $tmin2 = $timeIn[$i] ?? "";
                    $tmout2 = $timeOut[$i] ?? "";
                }
            }
    
            if (count($sessions) === 2 && $tmout1 === "" && $tmout2 !== "") {
                $tmout1 = $tmout2;
                $tmout2 = "";
            }
    
            $timeDiff = ($tmout1 ? timeDifference($tmin1, $tmout1) : 0) +
                        ($tmout2 ? timeDifference($tmin2, $tmout2) : 0);
    
            $sessionCount = (int)($tmout1 !== "") + (int)($tmout2 !== "");
            $elapsedTime = $timeDiff > 0 ? getHoursFromMinutes($timeDiff) . " [{$sessionCount}]"  : "";
    
            $attendanceData[] = [
                'sno'          => $sno++,
                'emp_name'     => $attnData->employee->name,
                'emp_code'     => $attnData->employee->code,
                'attn_date'    => displayDate($attnData->attn_date),
                'tmin1'        => displayTime($tmin1),
                'tmin2'        => displayTime($tmin2),
                'tmout1'       => displayTime($tmout1),
                'tmout2'       => displayTime($tmout2),
                'elapsed_time' => $elapsedTime
            ];
        }
    
        return $attendanceData;
    }
/* Attendance Report - Section End */


/* Item Wise Sales Report - Section Start */
    public function itemWiseSalesReport(Request $request)
    {
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $reportType = $request->input('reportType') ?? 'Count';

        $data = $this->getItemWiseSalesData($fromDate, $toDate, $reportType);

        // return response()->json([
        return view('reports.sales-itemwise-report', [
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
            'reportType' => $reportType,
            'records'    => $data['records'],
            'totals'     => $data['totals']
        ]);
    }

    public function getItemWiseSalesData($fromDate, $toDate, $reportType) {
        $productList1 = Product::select('id','name','tax_type')->where('type','Pouch')->orderBy('display_index')->get();
        $productList2 = Product::select('id','name','tax_type')->where('type','Product')->orderBy('display_index')->get();
        $productList3 = Product::select('id','name')->where('visible_bulkmilk',1)->orderBy('display_index')->get();

        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $jobWorkNums = JobWork::whereBetween('job_work_date', [$fromDate, $toDate])->pluck('job_work_num');
        
        $records = [];
        $this->generateItemWiseRecords($records, $productList1, "POUCH", $salesInvNums, $taxInvNums);
        $this->generateItemWiseRecords($records, $productList2, "PRODUCT", $salesInvNums, $taxInvNums);
        $this->generateBulkMilkRecords($records, $productList3, $bulkInvNums);
        $this->generateJobWorkRecords($records, $productList3, $jobWorkNums);

        $records = $this->getRecordsByType($records, $reportType);
        $totals = [];
        if($records)
            $totals = $this->calculateItemWiseTotals($records, $reportType);

        return [
            'records' => $records,
            'totals' => $totals
        ];
    }

    private function generateItemWiseRecords(&$records, $productList, $productType, $salesInvNums, $taxInvNums) {
        foreach($productList as $product) {
            $regular = $damage = $spoilage = $sample = $amount = [];
            $productId = $product->id;

            if($product->tax_type == "Exempted") {
                $regular  = $this->getSalesItemTotals($salesInvNums, $productId, "Regular");
                $damage   = $this->getSalesItemTotals($salesInvNums, $productId, "Damage");
                $spoilage = $this->getSalesItemTotals($salesInvNums, $productId, "Spoilage");
                $sample   = $this->getSalesItemTotals($salesInvNums, $productId, "Sample");
            }
            else if($product->tax_type == "Taxable") {
                $regular  = $this->getTaxItemTotals($taxInvNums, $productId, "Regular");
                $damage   = $this->getTaxItemTotals($taxInvNums, $productId, "Damage");
                $spoilage = $this->getTaxItemTotals($taxInvNums, $productId, "Spoilage");
                $sample   = $this->getTaxItemTotals($taxInvNums, $productId, "Sample");
            }

            $totalQty =  $regular['qty'] + $damage['qty'] + $spoilage['qty'] + $sample['qty'];
            if($totalQty) {
                $record = [];
                $record['product']      = $product->name;
                $record['type']         = $productType;
                $record['regular_qty']  = getTwoDigitPrecision($regular['qty'], null);
                $record['ghee_qty']     = null;
                $record['damage_qty']   = getTwoDigitPrecision($damage['qty'], null);
                $record['spoilage_qty'] = getTwoDigitPrecision($spoilage['qty'], null);
                $record['sample_qty']   = getTwoDigitPrecision($sample['qty'], null);
                $record['total_qty']    = getTwoDigitPrecision($record['regular_qty'] + $record['damage_qty'] + $record['spoilage_qty'] + $record['sample_qty']);
                $record['regular_amt']  = getTwoDigitPrecision($regular['amount'], null);
                $record['ghee_amt']     = null;
                $record['damage_amt']   = getTwoDigitPrecision($damage['amount'], null);
                $record['spoilage_amt'] = getTwoDigitPrecision($spoilage['amount'], null);
                $record['sample_amt']   = getTwoDigitPrecision($sample['amount'], null);
                $record['total_amt']    = getTwoDigitPrecision($record['regular_amt'] + $record['damage_amt'] + $record['spoilage_amt'] + $record['sample_amt']);

                if($productType == "PRODUCT") {
                    $record['ghee_qty'] = $record['regular_qty'];
                    $record['ghee_amt'] = $record['regular_amt'];
                    $record['regular_qty'] = null;
                    $record['regular_amt'] = null;
                }

                array_push($records, $record);
            }
        }
    }

    private function generateBulkMilkRecords(&$records, $productList, $invoiceNums) {
        foreach($productList as $product) {
            $summary = BulkMilkOrderItem::whereIn('invoice_num', $invoiceNums)
                ->where('product_id', $product->id)
                ->selectRaw("SUM(qty_ltr) as qty, SUM(amount) as amount")
                ->first();

            if($summary['qty']) {
                $record = [];
                $record['product']      = $product->name;
                $record['type']         = "BULKMILK";
                $record['regular_qty']  = getTwoDigitPrecision($summary['qty'], null);
                $record['ghee_qty']     = null;
                $record['damage_qty']   = null;
                $record['spoilage_qty'] = null;
                $record['sample_qty']   = null;
                $record['total_qty']    = $record['regular_qty'];
                $record['regular_amt']  = getTwoDigitPrecision($summary['amount'], null);
                $record['ghee_amt']     = null;
                $record['damage_amt']   = null;
                $record['spoilage_amt'] = null;
                $record['sample_amt']   = null;
                $record['total_amt']    = getTwoDigitPrecision($summary['amount']);

                array_push($records, $record);
            }
        }
    }

    private function generateJobWorkRecords(&$records, $productList, $jobWorkNums) {
        foreach($productList as $product) {
            $summary = JobWorkItem::whereIn('job_work_num', $jobWorkNums)
                ->where('product_id', $product->id)
                ->selectRaw("SUM(qty_ltr) as qty")
                ->first();

            if($summary['qty']) {
                $record = [];
                $record['product']      = $product->name;
                $record['type']         = "CONVERSION";
                $record['regular_qty']  = getTwoDigitPrecision($summary['qty'], null);
                $record['ghee_qty']     = null;
                $record['damage_qty']   = null;
                $record['spoilage_qty'] = null;
                $record['sample_qty']   = null;
                $record['total_qty']    = $record['regular_qty'];
                $record['regular_amt']  = null;
                $record['ghee_amt']     = null;
                $record['damage_amt']   = null;
                $record['spoilage_amt'] = null;
                $record['sample_amt']   = null;
                $record['total_amt']    = "0.00";

                array_push($records, $record);
            }
        }
    }

    private function getSalesItemTotals($invoiceNums, $productId, $category) {
        $summary = SalesInvoiceItem::whereIn('invoice_num', $invoiceNums)
            ->where('product_id', $productId)
            ->where('item_category',$category)
            ->selectRaw("SUM(qty) as qty, SUM(amount) as amount")
            ->first();
        return $summary;
    }

    private function getTaxItemTotals($invoiceNums, $productId, $category) {
        $summary = TaxInvoiceItem::whereIn('invoice_num', $invoiceNums)
            ->where('product_id', $productId)
            ->where('item_category',$category)
            ->selectRaw("SUM(qty) as qty, SUM(tot_amt) as amount")
            ->first();
        return $summary;
    }

    private function calculateItemWiseTotals($records, $type)
    {
        $totals = [
            'regular_qty'  => 0,
            'ghee_qty'     => 0,
            'damage_qty'   => 0,
            'spoilage_qty' => 0,
            'sample_qty'   => 0,
            'total_qty'    => 0,
            'regular_amt'  => 0,
            'ghee_amt'     => 0,
            'damage_amt'   => 0,
            'spoilage_amt' => 0,
            'sample_amt'   => 0,
            'total_amt'    => 0
        ];

        foreach ($records as $record) {
            // Sum quantities if the type is 'Count' or 'Both'
            if ($type == 'Count' || $type == 'Both') {
                $totals['regular_qty']  += (float)($record['regular_qty'] ?? 0);
                $totals['ghee_qty']     += (float)($record['ghee_qty'] ?? 0);
                $totals['damage_qty']   += (float)($record['damage_qty'] ?? 0);
                $totals['spoilage_qty'] += (float)($record['spoilage_qty'] ?? 0);
                $totals['sample_qty']   += (float)($record['sample_qty'] ?? 0);
                $totals['total_qty']    += (float)$record['total_qty'];
            }
    
            // Sum amounts if the type is 'Amount' or 'Both'
            if ($type == 'Amount' || $type == 'Both') {
                $totals['regular_amt']  += (float)($record['regular_amt'] ?? 0);
                $totals['ghee_amt']     += (float)($record['ghee_amt'] ?? 0);
                $totals['damage_amt']   += (float)($record['damage_amt'] ?? 0);
                $totals['spoilage_amt'] += (float)($record['spoilage_amt'] ?? 0);
                $totals['sample_amt']   += (float)($record['sample_amt'] ?? 0);
                $totals['total_amt']    += (float)$record['total_amt'];
            }
        }
    
        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = getTwoDigitPrecision($value);
        }

        // Filter totals based on the type
        if ($type == 'Count') {
            return [
                'damage_qty'   => $totals['damage_qty'],
                'spoilage_qty' => $totals['spoilage_qty'],
                'sample_qty'   => $totals['sample_qty'],
                'ghee_qty'     => $totals['ghee_qty'],
                'regular_qty'  => $totals['regular_qty'],
                'total_qty'    => $totals['total_qty'],
            ];
        }
        elseif ($type == 'Amount') {
            return [
                'damage_amt'   => $totals['damage_amt'],
                'spoilage_amt' => $totals['spoilage_amt'],
                'sample_amt'   => $totals['sample_amt'],
                'ghee_amt'     => $totals['ghee_amt'],
                'regular_amt'  => $totals['regular_amt'],
                'total_amt'    => $totals['total_amt'],
            ];
        }
        else {
            return [
                'damage_qty'   => $totals['damage_qty'],
                'damage_amt'   => $totals['damage_amt'],
                'spoilage_qty' => $totals['spoilage_qty'],
                'spoilage_amt' => $totals['spoilage_amt'],
                'sample_qty'   => $totals['sample_qty'],
                'sample_amt'   => $totals['sample_amt'],
                'ghee_qty'     => $totals['ghee_qty'],
                'ghee_amt'     => $totals['ghee_amt'],
                'regular_qty'  => $totals['regular_qty'],
                'regular_amt'  => $totals['regular_amt'],
                'total_qty'    => $totals['total_qty'],
                'total_amt'    => $totals['total_amt']
            ];
        }

        return $totals;
    }

    private function getRecordsByType($records, $type)
    {
        $result = [];
        $sno = 1; // Initialize serial number

        foreach ($records as $record) {
            if ($type == 'Count') {
                $result[] = [
                    'sno'            => $sno++,  // Add serial number
                    'product'        => $record['product'],
                    'damage_qty'     => $record['damage_qty'],
                    'spoilage_qty'   => $record['spoilage_qty'],
                    'sample_qty'     => $record['sample_qty'],
                    'ghee_qty'       => $record['ghee_qty'],
                    'regular_qty'    => $record['regular_qty'],
                    'total_qty'      => $record['total_qty'],
                    'type'           => $record['type']
                ];
            } 
            elseif ($type == 'Amount') {
                $result[] = [
                    'sno'            => $sno++,  // Add serial number
                    'product'        => $record['product'],
                    'damage_amt'     => $record['damage_amt'],
                    'spoilage_amt'   => $record['spoilage_amt'],
                    'sample_amt'     => $record['sample_amt'],
                    'ghee_amt'       => $record['ghee_amt'],
                    'regular_amt'    => $record['regular_amt'],
                    'total_amt'      => $record['total_amt'],
                    'type'           => $record['type']
                ];
            } 
            elseif ($type == 'Both') {
                $result[] = [
                    'sno'            => $sno++,  // Add serial number
                    'product'        => $record['product'],
                    'damage_qty'     => $record['damage_qty'],
                    'damage_amt'     => $record['damage_amt'],
                    'spoilage_qty'   => $record['spoilage_qty'],
                    'spoilage_amt'   => $record['spoilage_amt'],
                    'sample_qty'     => $record['sample_qty'],
                    'sample_amt'     => $record['sample_amt'],
                    'ghee_qty'       => $record['ghee_qty'],
                    'ghee_amt'       => $record['ghee_amt'],
                    'regular_qty'    => $record['regular_qty'],
                    'regular_amt'    => $record['regular_amt'],
                    'total_qty'      => $record['total_qty'],
                    'total_amt'      => $record['total_amt'],
                    'type'           => $record['type']
                ];
            }
        }

        return $result;
    }
/* Item Wise Sales Report - Section End */


/* HSN Wise Sales Report - Section Start */
    public function hsnWiseSalesReport(Request $request)
    {
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $reportType = $request->input('reportType') ?? 'Format1';

        if($reportType == "Format1")
            $data = $this->getHsnWiseDataByProduct($fromDate, $toDate);
        else
            $data = $this->getHsnWiseDataByName($fromDate, $toDate);

        // return response()->json([
        return view('reports.sales-hsnwise-report', [
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
            'reportType' => $reportType,
            'records'    => $data['records'],
            'totals'     => $data['totals']
        ]);
    }

    public function getHsnWiseDataByProduct($fromDate, $toDate) {
        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        
        $productList1 = Product::select('id','name','hsn_code','tax_type','gst')->where('visible_invoice',1)->orderBy('hsn_code')->orderBy('display_index')->get();
        $productList2 = Product::select('id','name','hsn_code')->where('visible_bulkmilk',1)->orderBy('hsn_code')->orderBy('display_index')->get();

        $records = [];
        $this->generateHsnWiseRecords($records, $productList1, $salesInvNums, $taxInvNums);
        $this->generateHsnWiseBulkMilkRecords($records, $productList2, $bulkInvNums);

        $totals = [];
        if($records) {
            // Add serial number and make it the first column
            foreach ($records as $index => &$record) {
                // Add the serial number
                $record = ['sno' => $index + 1] + $record;  // Adding sno as the first column
            }
            // Generate Total Row
            $totals = $this->calculateHsnWiseTotalsFormat1($records);
        }

        return [
            'records' => $records,
            'totals' => $totals
        ];
    }

    private function generateHsnWiseRecords(&$records, $productList, $salesInvNums, $taxInvNums) {        
        foreach ($productList as $product) {
            // Fetch exempted summary from SalesInvoiceItem
            $salesSummary = SalesInvoiceItem::whereIn('invoice_num', $salesInvNums)
                ->where('product_id', $product->id)
                ->selectRaw("SUM(qty) as qty, SUM(amount) as amount")
                ->first();

            if ($salesSummary && $salesSummary->qty != 0) {
                $records[] = [
                    'hsn_code'    => $product->hsn_code,
                    'product'     => $product->name,
                    'type'        => 'Goods',
                    'group'       => $this->getProductGroup($product->id),
                    'uqc'         => $this->getUnitName($product->id),
                    'total_qty'   => getTwoDigitPrecision($salesSummary->qty),
                    'total_value' => getTwoDigitPrecision($salesSummary->amount),
                    'tax_rate'    => "0 %",
                    'taxable_amt' => getTwoDigitPrecision($salesSummary->amount),
                    'igst'        => "",
                    'cgst'        => "",
                    'sgst'        => "",
                    'cess_amt'    => "",
                    'total_tax'   => "",
                ];
            }

            // Fetch taxable summary from TaxInvoiceItem
            $taxSummary = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                ->where('product_id', $product->id)
                ->selectRaw("SUM(qty) as qty, SUM(tot_amt) as amount")
                ->first();            

            if ($taxSummary && $taxSummary->qty != 0) {
                $tax_data = $this->getTaxDataByProduct($taxInvNums, $product->id);
                foreach($tax_data as $data) {
                    $records[] = [
                        'hsn_code'    => $product->hsn_code,
                        'product'     => $product->name,
                        'type'        => 'Goods',
                        'group'       => $this->getProductGroup($product->id),
                        'uqc'         => $this->getUnitName($product->id),
                        'total_qty'   => getTwoDigitPrecision($taxSummary->qty),
                        'total_value' => getTwoDigitPrecision($taxSummary->amount),
                        'tax_rate'    => $data['gst'] . " %",
                        'taxable_amt' => $data['taxable_amt'],
                        'igst'        => $data['igst'],
                        'cgst'        => $data['cgst'],
                        'sgst'        => $data['sgst'],
                        'cess_amt'    => "",
                        'total_tax'   => $data['total_tax'],
                    ];
                }
            }
        }
    }    
    
    private function generateHsnWiseBulkMilkRecords(&$records, $productList, $invoiceNums) {
        foreach ($productList as $product) {
            $summary = BulkMilkOrderItem::whereIn('invoice_num', $invoiceNums)
                ->where('product_id', $product->id)
                ->selectRaw("SUM(qty_ltr) as qty, SUM(amount) as amount")
                ->first();
    
            if ($summary['qty'] != 0) {
                $existingRecordIndex = null;
    
                // Search for the existing record by product name
                foreach ($records as $index => $record) {
                    if ($record['product'] === $product->name) {
                        $existingRecordIndex = $index;
                        break;
                    }
                }
    
                if ($existingRecordIndex !== null) {
                    $record = &$records[$existingRecordIndex];

                    // Update the existing record by summing up the values 
                    $record['total_qty']   += $summary->qty;
                    $record['total_value'] += $summary->amount;
                    $record['taxable_amt'] += $summary->amount;

                    $record['total_qty'] = getTwoDigitPrecision($record['total_qty']);
                    $record['total_value'] = getTwoDigitPrecision($record['total_value']);
                    $record['taxable_amt'] = getTwoDigitPrecision($record['taxable_amt']);

                    // Break the reference to avoid carrying it into next loop
                    unset($record);
                } 
                else {
                    // Create a new record and push it to the array
                    $record = [];
                    $record['hsn_code']    = $product->hsn_code;
                    $record['product']     = $product->name;
                    $record['type']        = "Goods";
                    $record['group']       = $this->getProductGroup($product->id);
                    $record['uqc']         = $this->getUnitName($product->id);
                    $record['total_qty']   = getTwoDigitPrecision($summary->qty);
                    $record['total_value'] = getTwoDigitPrecision($summary->amount);
                    $record['tax_rate']    = "0 %";
                    $record['taxable_amt'] = getTwoDigitPrecision($summary->amount);
                    $record['igst']        = "";
                    $record['cgst']        = "";
                    $record['sgst']        = "";
                    $record['cess_amt']    = "";
                    $record['total_tax']   = "";
                    array_push($records, $record);
                }
            }
        }
    }
    
    private function calculateHsnWiseTotalsFormat1($records)
    {
        $totals = [
            'total_qty'   => 0,
            'total_value' => 0,
            'taxable_amt' => 0,
            'igst'        => 0,
            'cgst'        => 0,
            'sgst'        => 0,
            'total_tax'   => 0
        ];

        foreach ($records as $record) {
            $totals['total_qty']   += (float)($record['total_qty'] ?? 0);
            $totals['total_value'] += (float)($record['total_value'] ?? 0);
            $totals['taxable_amt'] += (float)($record['taxable_amt'] ?? 0);
            $totals['igst']        += (float)($record['igst'] ?? 0);
            $totals['cgst']        += (float)($record['cgst'] ?? 0);
            $totals['sgst']        += (float)($record['sgst'] ?? 0);
            $totals['total_tax']   += (float)($record['total_tax'] ?? 0);
        }
    
        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumberWithDecimal($value);
        }

        return $totals;
    }

    private function getTaxDataByHsnCode($taxInvNums, $hsn_code) {
        $data = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                ->where('hsn_code', $hsn_code)
                ->where('item_category', 'Regular')
                ->selectRaw('SUM(amount) as taxable_amt,
                             SUM(amount * sgst / 100) as sgst,
                             SUM(amount * cgst / 100) as cgst,
                             SUM(amount * igst / 100) as igst')
                ->first();
    
        // Handle possible null values during addition
        $data->total_tax = ($data->sgst ?? 0) + ($data->cgst ?? 0) + ($data->igst ?? 0);
        $data->total_amt = $data->taxable_amt + $data->total_tax;

        // Round to two decimal precision
        $data = [
            'taxable_amt' => getTwoDigitPrecision($data->taxable_amt),
            'sgst'        => getTwoDigitPrecision($data->sgst, ''),
            'cgst'        => getTwoDigitPrecision($data->cgst, ''),
            'igst'        => getTwoDigitPrecision($data->igst, ''),
            'total_tax'   => getTwoDigitPrecision($data->total_tax),
            'total_amt'   => getTwoDigitPrecision($data->total_amt)
        ];
    
        return $data;
    }

    private function getTaxDataByProduct($tax_inv_nums, $product_id) {
        $records = TaxInvoiceItem::whereIn('invoice_num', $tax_inv_nums)
            ->where('product_id', $product_id)
            ->where('item_category', 'Regular')
            ->selectRaw('gst,
                        SUM(amount) as taxable_amt,
                        SUM(amount * sgst / 100) as sgst,
                        SUM(amount * cgst / 100) as cgst,
                        SUM(amount * igst / 100) as igst')
            ->groupBy('gst')
            ->get();

        $result = [];

        foreach ($records as $data) {
            $total_tax = ($data->sgst ?? 0) + ($data->cgst ?? 0) + ($data->igst ?? 0);
            $total_amt = ($data->taxable_amt ?? 0) + $total_tax;

            $result[] = [
                'gst'         => $data->gst,
                'taxable_amt' => getTwoDigitPrecision($data->taxable_amt),
                'sgst'        => getTwoDigitPrecision($data->sgst, ''),
                'cgst'        => getTwoDigitPrecision($data->cgst, ''),
                'igst'        => getTwoDigitPrecision($data->igst, ''),
                'total_tax'   => getTwoDigitPrecision($total_tax),
                'total_amt'   => getTwoDigitPrecision($total_amt),
            ];
        }

        return $result;
    }

    private function getUnitName($product_id) {
        $unit_id = ProductUnit::where('product_id', $product_id)->where('prim_unit', 1)->value('unit_id');
        $unit_name = UOM::where('id', $unit_id)->value('unit_name');
        return $unit_name;
    }

    private function getProductGroup($product_id)
    {
        $product = Product::with('prod_group:id,name')->find($product_id);
        return $product->prod_group->name ?? null;
    }

    public function getHsnWiseDataByName($fromDate, $toDate) {
        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        
        $hsnList = GstMaster::select('hsn_code','description','tax_type')->get();
        $records = [];
        foreach ($hsnList as $hsn) {
            $hsnCode = $hsn->hsn_code;
            $hsnName = $hsn->description;
            $this->generateHsnWiseSalesRecord($records, $salesInvNums, $hsn);
            $this->generateHsnWiseTaxRecord($records, $taxInvNums, $hsn);
            $this->generateHsnWiseBulkMilkRecord($records, $bulkInvNums, $hsn);
        }

        $totals = [];
        if($records) {
            // Add serial number and make it the first column
            foreach ($records as $index => &$record) {
                // Add the serial number
                $record = ['sno' => $index + 1] + $record;  // Adding sno as the first column
            }
            // Calculate Totals
            $totals = $this->calculateHsnWiseTotalsFormat2($records);
        }

        return [
            'records' => $records,
            'totals' => $totals
        ];
    }

    private function generateHsnWiseSalesRecord(&$records, $salesInvNums, $hsn) {        
        $summary = SalesInvoiceItem::whereIn('invoice_num', $salesInvNums)
            ->where('hsn_code', $hsn->hsn_code)
            ->selectRaw("SUM(qty) as qty, SUM(amount) as amount")
            ->first();

        if ($summary->qty) {
            $record['hsn_name']        = $hsn->description;
            $record['hsn_code']        = $hsn->hsn_code;
            $record['pouch_qty']       = getTwoDigitPrecision($summary->qty);
            $record['bulk_qty']        = "";
            $record['tax_qty']         = "";
            $record['total_qty']       = "";
            $record['pouch_sales_amt'] = getTwoDigitPrecision($summary->amount);
            $record['taxable_amt']     = "";
            $record['sgst']            = "";
            $record['cgst']            = "";
            $record['igst']            = "";
            $record['tax_sales_amt']   = "";
            $record['bulk_sales_amt']  = "";
            $record['total_sales_amt'] = "";

            array_push($records, $record);
        }
    }

    private function generateHsnWiseTaxRecord(&$records, $taxInvNums, $hsn) {
        $sumQty = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
            ->where('hsn_code', $hsn->hsn_code)
            ->sum('qty');

        if ($sumQty > 0) {
            $tax_data = $this->getTaxDataByHsnCode($taxInvNums, $hsn->hsn_code);
            $record['hsn_name']        = $hsn->description;
            $record['hsn_code']        = $hsn->hsn_code;
            $record['pouch_qty']       = "";
            $record['bulk_qty']        = "";
            $record['tax_qty']         = getTwoDigitPrecision($sumQty);
            $record['total_qty']       = "";
            $record['pouch_sales_amt'] = "";
            $record['taxable_amt']     = $tax_data['taxable_amt'];
            $record['sgst']            = $tax_data['cgst'];
            $record['cgst']            = $tax_data['sgst'];
            $record['igst']            = $tax_data['igst'];
            $record['tax_sales_amt']   = $tax_data['total_amt'];
            $record['bulk_sales_amt']  = "";
            $record['total_sales_amt'] = "";

            array_push($records, $record);
        }
    }

    private function generateHsnWiseBulkMilkRecord(&$records, $bulkInvNums, $hsn) {
        $summary = BulkMilkOrderItem::whereIn('invoice_num', $bulkInvNums)
            ->where('hsn_code', $hsn->hsn_code)
            ->selectRaw("SUM(qty_ltr) as qty, SUM(amount) as amount")
            ->first();

        if ($summary->qty) {
            // Initialize a flag to check if the record was found
            $isRecordFound = false;

            // Loop through each record in the array
            foreach ($records as $index => $record) {
                // Check if the hsn_code matches
                if ($record['hsn_code'] === $hsn->hsn_code) {
                    // Update the record with new data
                    $records[$index]['bulk_qty'] = getTwoDigitPrecision($summary->qty);
                    $records[$index]['bulk_sales_amt'] = getTwoDigitPrecision($summary->amount);
                    $isRecordFound = true;
                    break;
                }
            }

            // If no matching record was found, add the new record to the array
            if (!$isRecordFound) {
                $record['hsn_name']        = $hsn->description;
                $record['hsn_code']        = $hsn->hsn_code;
                $record['pouch_qty']       = "";
                $record['bulk_qty']        = getTwoDigitPrecision($summary->qty);
                $record['tax_qty']         = "";
                $record['total_qty']       = "";
                $record['pouch_sales_amt'] = "";
                $record['taxable_amt']     = "";
                $record['sgst']            = "";
                $record['cgst']            = "";
                $record['igst']            = "";
                $record['tax_sales_amt']   = "";
                $record['bulk_sales_amt']  = getTwoDigitPrecision($summary->amount);
                $record['total_sales_amt'] = "";

                array_push($records, $record);
            }
        }
    }

    private function calculateHsnWiseTotalsFormat2(&$records)
    {
        $totals = [
            'pouch_qty'       => 0,
            'bulk_qty'        => 0,
            'tax_qty'         => 0,
            'total_qty'       => 0,
            'pouch_sales_amt' => 0,
            'taxable_amt'     => 0,
            'sgst'            => 0,
            'cgst'            => 0,
            'igst'            => 0,
            'tax_sales_amt'   => 0,
            'bulk_sales_amt'  => 0,
            'total_sales_amt' => 0
        ];
    
        // Use index reference to update records directly
        foreach ($records as $index => &$record) {
            $pouchQty = (float)($record['pouch_qty'] ?? 0);
            $taxQty   = (float)($record['tax_qty'] ?? 0);
            $bulkQty  = (float)($record['bulk_qty'] ?? 0);
            $pouchAmt = (float)($record['pouch_sales_amt'] ?? 0);
            $taxAmt   = (float)($record['tax_sales_amt'] ?? 0);
            $bulkAmt  = (float)($record['bulk_sales_amt'] ?? 0);
    
            // Update the original $records array with calculated totals
            $records[$index]['total_qty']       = getTwoDigitPrecision($pouchQty + $taxQty + $bulkQty);
            $records[$index]['total_sales_amt'] = getTwoDigitPrecision($pouchAmt + $taxAmt + $bulkAmt);
    
            // Update the totals array
            $totals['pouch_qty']       += $pouchQty;
            $totals['bulk_qty']        += $bulkQty;
            $totals['tax_qty']         += $taxQty;
            $totals['total_qty']       += (float)($records[$index]['total_qty'] ?? 0);
            $totals['pouch_sales_amt'] += $pouchAmt;
            $totals['taxable_amt']     += (float)($record['taxable_amt'] ?? 0);
            $totals['sgst']            += (float)($record['sgst'] ?? 0);
            $totals['cgst']            += (float)($record['cgst'] ?? 0);
            $totals['igst']            += (float)($record['igst'] ?? 0);
            $totals['tax_sales_amt']   += $taxAmt;
            $totals['bulk_sales_amt']  += $bulkAmt;
            $totals['total_sales_amt'] += (float)($records[$index]['total_sales_amt'] ?? 0);
        }
    
        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = getTwoDigitPrecision($value);
        }
    
        return $totals;
    }
/* HSN Wise Sales Report - Section End */


/* Tax Wise Sales Report - Section Start */
    public function taxWiseSalesReport(Request $request)
    {
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $reportType = $request->input('reportType') ?? 'Format1';

        if($reportType == "Format1")
            $data = $this->getTaxWiseDataFormat1($fromDate, $toDate);
        else
            $data = $this->getTaxWiseDataFormat2($fromDate, $toDate);

        // return response()->json([
        return view('reports.sales-taxwise-report', [
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
            'reportType' => $reportType,
            'records'    => $data['records'],
            'totals'     => $data['totals']
        ]);
    }

    public function getTaxWiseDataFormat1($fromDate, $toDate) {
        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        
        $hsnList = GstMaster::select('hsn_code','description')->get();
        $records = [];
        foreach ($hsnList as $hsn) {
            $this->generateTaxWiseSalesRecords($records, $hsn, $salesInvNums, $bulkInvNums);
            $this->generateTaxWiseTaxRecords($records, $hsn, $taxInvNums);
        }

        $totals = [];
        if($records) {
            $this->addSNoColumn($records);
            $totals = $this->calculateTaxWiseTotals($records);
        }

        return [
            'records' => $records,
            'totals' => $totals
        ];
    }

    private function generateTaxWiseSalesRecords(&$records, $hsn, $salesInvNums, $bulkInvNums) {
        $amount = SalesInvoiceItem::whereIn('invoice_num', $salesInvNums)->where('hsn_code', $hsn->hsn_code)->sum('amount');
        $amount += BulkMilkOrderItem::whereIn('invoice_num', $bulkInvNums)->where('hsn_code', $hsn->hsn_code)->sum('amount');
        if ($amount) {
            $amt = getTwoDigitPrecision($amount);
            $records[] = [
                'hsn_name'    => $hsn->description,
                'hsn_code'    => $hsn->hsn_code,
                'tax_rate'    => "0 %",                
                'taxable_amt' => $amt, 
                'sgst'        => "",                   
                'cgst'        => "",                  
                'igst'        => "",                   
                'tax_amt'     => "",                    
                'gross_amt'   => $amt,
                'discount'    => "",
                'net_amt'     => $amt,
            ];            
        }
    }

    private function generateTaxWiseTaxRecords(&$records, $hsn, $taxInvNums) {
        $gstPerc = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                        ->where('hsn_code', $hsn->hsn_code)
                        ->distinct('gst')
                        ->get(['gst']);

        foreach($gstPerc as $item) {
            $summary = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                        ->where('hsn_code', $hsn->hsn_code)
                        ->where('gst', $item->gst)
                        ->selectRaw("SUM(amount) as amount, 
                                     SUM(tax_amt) as tax_amt, 
                                     SUM(tot_amt) as tot_amt, 
                                     SUM(amount * sgst / 100) as sgst,
                                     SUM(amount * cgst / 100) as cgst,
                                     SUM(amount * igst / 100) as igst")
                        ->first();

            if ($summary->amount) {
                $records[] = [
                    'hsn_name'    => $hsn->description,
                    'hsn_code'    => $hsn->hsn_code,
                    'tax_rate'    => $item->gst . " %",
                    'taxable_amt' => getTwoDigitPrecision($summary->amount),
                    'sgst'        => getTwoDigitPrecision($summary->sgst, ''),
                    'cgst'        => getTwoDigitPrecision($summary->cgst, ''),
                    'igst'        => getTwoDigitPrecision($summary->igst, ''),
                    'tax_amt'     => getTwoDigitPrecision($summary->tax_amt),
                    'gross_amt'   => getTwoDigitPrecision($summary->amount + $summary->tax_amt),
                    'discount'    => "",
                    'net_amt'     => getTwoDigitPrecision($summary->tot_amt),
                ];                
            }
        }
    }

    private function calculateTaxWiseTotals($records)
    {
        $totals = [
            'taxable_amt' => 0,
            'sgst'        => 0,
            'cgst'        => 0,
            'igst'        => 0,
            'tax_amt'     => 0,
            'gross_amt'   => 0,
            'discount'    => 0,
            'net_amt'     => 0,
        ];

        foreach ($records as $record) {
            $totals['taxable_amt'] += (float)($record['taxable_amt'] ?? 0);
            $totals['sgst']        += (float)($record['sgst'] ?? 0);
            $totals['cgst']        += (float)($record['cgst'] ?? 0);
            $totals['igst']        += (float)($record['igst'] ?? 0);
            $totals['tax_amt']     += (float)($record['tax_amt'] ?? 0);
            $totals['gross_amt']   += (float)($record['gross_amt'] ?? 0);
            $totals['discount']    += (float)($record['discount'] ?? 0);
            $totals['net_amt']     += (float)($record['net_amt'] ?? 0);
        }
    
        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumberWithDecimal($value);
        }

        return $totals;
    }

    public function getTaxWiseDataFormat2($fromDate, $toDate) {
        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])->where('invoice_status','<>','Cancelled')->pluck('invoice_num');

        $records = [];
        $this->generateZeroTaxWiseRecords($records, $salesInvNums, $bulkInvNums);
        $this->generateNonZeroTaxWiseRecords($records, $taxInvNums);
        
        $totals = [];
        if($records) {
            $this->addSNoColumn($records);
            $totals = $this->calculateTaxWiseTotals($records);
        }

        return [
            'records' => $records,
            'totals' => $totals
        ];
    }    

    private function generateZeroTaxWiseRecords(&$records, $salesInvNums, $bulkInvNums) {
        $amount = SalesInvoiceItem::whereIn('invoice_num', $salesInvNums)->sum('amount');
        $amount += BulkMilkOrderItem::whereIn('invoice_num', $bulkInvNums)->sum('amount');
        if ($amount) {
            $records[] = [
                'tax_rate'    => "0 %",
                'taxable_amt' => getTwoDigitPrecision($amount),
                'sgst'        => "",
                'cgst'        => "",
                'igst'        => "",
                'tax_amt'     => "",
                'gross_amt'   => getTwoDigitPrecision($amount),
                'discount'    => "",
                'net_amt'     => getTwoDigitPrecision($amount),
            ];
        }
    }

    private function generateNonZeroTaxWiseRecords(&$records, $taxInvNums) {
        $gstPerc = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                    ->distinct('gst')
                    ->orderBy('gst', 'asc')
                    ->get(['gst']);

        foreach($gstPerc as $item) {
            $summary = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                        ->where('gst', $item->gst)
                        ->selectRaw("SUM(amount) as amount, 
                                     SUM(tax_amt) as tax_amt, 
                                     SUM(tot_amt) as tot_amt, 
                                     SUM(amount * sgst / 100) as sgst,
                                     SUM(amount * cgst / 100) as cgst,
                                     SUM(amount * igst / 100) as igst")
                        ->first();

            if ($summary->amount) {
                $records[] = [
                    'tax_rate'    => $item->gst . " %",
                    'taxable_amt' => getTwoDigitPrecision($summary->amount),
                    'sgst'        => getTwoDigitPrecision($summary->sgst,''),
                    'cgst'        => getTwoDigitPrecision($summary->cgst,''),
                    'igst'        => getTwoDigitPrecision($summary->igst,''),
                    'tax_amt'     => getTwoDigitPrecision($summary->tax_amt),
                    'gross_amt'   => getTwoDigitPrecision($summary->amount + $summary->tax_amt),
                    'discount'    => "",
                    'net_amt'     => getTwoDigitPrecision($summary->tot_amt),
                ];
            }
        }
    }

    private function addSNoColumn(&$records) {
        // Add serial number and make it the first column
        foreach ($records as $index => &$record) {
            // Add the serial number
            $record = ['sno' => $index + 1] + $record;  // Adding sno as the first column
        }
    }
/* Tax Wise Sales Report - Section End */


/* Invoice Report - Section Start */
    public function invoiceReport(Request $request)
    {
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $routeId = $request->input('route') ?? 0;
        
        $routes = MRoute::select('id', 'name')->orderBy('name')->get();
        $reportData = $this->getInvoiceData($fromDate, $toDate, $routeId, $routes);

        return view('reports.invoice-report', [
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'routeId'        => $routeId,
            'routes'         => $routes,
            'reportData'     => $reportData['data'],
            'grandTotals'    => $reportData['totals'],
            'routeRecords'   => $reportData['routeRecords'],
            'payModeRecords' => $reportData['payModeRecords']
        ]);
    }

    public function getInvoiceData($fromDate, $toDate, $routeId, $routes)
    {
        $data = [];
        $totals = [];
        $payModeRecords = [];
        $routeRecords = [];

        if ($routeId) {
            $data[] = $this->getInvoiceReportData([$fromDate, $toDate], $routeId);
        } else {
            $data[] = $this->getInvoiceReportData([$fromDate, $toDate], -1);
            $data[] = $this->getInvoiceReportData([$fromDate, $toDate], -2);
            foreach ($routes as $route) {
                $data[] = $this->getInvoiceReportData([$fromDate, $toDate], $route->id);
            }

            $totals = $this->getInvoiceReportTotals($data);
            $routeRecords = $this->getRouteRecordsForIR($data);
            $payModeRecords = $this->getPaymentModeRecordsForIR($data);
        }

        return [
            'data'          => $data,
            'totals'        => $totals,
            'routeRecords'  => $routeRecords,
            'payModeRecords' => $payModeRecords
        ];
    }

    private function getInvoiceReportData($dates, $routeId) {
        // Determine routeName and customer group based on routeId
        if ($routeId == -1) {
            $routeName = "Company";
            $customerGroup = 'Company';
        }
        elseif ($routeId == -2) {
            $routeName = "Function";
            $customerGroup = 'Function';
        } 
        else {
            $routeName = strtoupper(MRoute::where('id', $routeId)->value('name'));
            $customerGroup = ['Retailer', 'Distributor', 'Outlet'];
        }

        if($routeId == -1 || $routeId == -2) {
            $customers = Customer::select('id', 'customer_name', 'payment_mode')
                ->where('group', $customerGroup)
                ->orderBy('customer_name')
                ->get();
        }        
        else {
            $customers = Customer::select('id', 'customer_name', 'payment_mode')
                ->where('route_id', $routeId)
                ->whereIn('group', $customerGroup)
                ->orderBy('customer_name')
                ->get();
        }

        $records =  $this->getInvoiceReportRecords($customers, $dates);

        $data = array_merge(['route' => $routeName], $records);

        return $data; 
    }

    private function getInvoiceReportRecords($customers, $dates) {
        $data = [];
        $customerIds = $customers->pluck('id');
        $this->generateRecordsForIR($data, $dates, $customerIds, SalesInvoice::class);
        $this->generateRecordsForIR($data, $dates, $customerIds, TaxInvoice::class);
        $this->generateRecordsForIR($data, $dates, $customerIds, BulkMilkOrder::class, true);

        $groupedRecords = $this->getRouteDataForIR($data);
        $routeTotals = $this->getRouteTotalsForIR($groupedRecords);

        return [ 
            'routeRecords' => $groupedRecords,
            'routeTotals'  => $routeTotals
        ];
    }

    private function generateRecordsForIR(&$data, $dates, $customerIds, $model, $isBulkMilk = false) // IR - Invoice Report
    {
        // Define the basic fields to select
        $fields = ['id', 'invoice_num', 'invoice_date', 'customer_id', 'net_amt'];

        // If it's not a bulk milk order, include 'qty' in the select fields
        if (!$isBulkMilk) {
            $fields[] = 'qty';
        }

        // Fetch the invoices
        $invoices = $model::select($fields)
            ->with('customer:id,customer_name,payment_mode')
            ->whereBetween('invoice_date', $dates)
            ->whereIn('customer_id', $customerIds)
            ->where('invoice_status','<>','Cancelled')
            ->get();

        foreach ($invoices as $invoice) {
            if ($isBulkMilk) // If it's a Bulk Milk Order, calculate qty separately
                $qty = BulkMilkOrderItem::where('invoice_num', $invoice->invoice_num)->sum('qty_ltr');
            else // For regular orders, qty is now available
                $qty = $invoice->qty;

            // Create the record array
            $record['date']     = $invoice->invoice_date;
            $record['customer'] = $invoice->customer->customer_name;
            $record['pay_mode'] = $invoice->customer->payment_mode;
            $record['inv_num']  = $invoice->invoice_num;
            $record['qty']      = getTwoDigitPrecision($qty);
            $record['amount']   = getTwoDigitPrecision($invoice->net_amt);

            // Add the record to the data array
            $data[] = $record;
        }
    }

    private function getRouteDataForIR($data)
    {
        $payModes = ['Cash & Carry', 'Bill to Bill', 'Weekly', 'Twice Monthly', 'Monthly'];
        $groupedData = [];

        // Group the data by payment mode
        $grouped = collect($data)
            ->groupBy('pay_mode') // Group the data by pay_mode
            ->sortBy(function ($value, $key) use ($payModes) {
                return array_search($key, $payModes); // Sort according to the given pay_mode order
            });

        // Process each payment mode
        foreach ($payModes as $payMode) {
            if (isset($grouped[$payMode])) {
                $sortedData = collect($grouped[$payMode])
                    ->sortBy(function ($item) {
                        return [$item['date'], $item['customer']]; // Sort by date first, then by customer
                    })
                    ->values() // Reset keys after sorting
                    ->all(); // Convert to array

                // Calculate totals for qty and amount
                $totalQty = collect($sortedData)->sum('qty'); // Sum the quantities
                $totalAmount = collect($sortedData)->sum('amount'); // Sum the amounts

                // Build the output structure
                $groupedData[$payMode] = [
                    'records' => $sortedData, // Add records
                    'totals' => [
                        'qty' => getTwoDigitPrecision($totalQty),
                        'amount' => getTwoDigitPrecision($totalAmount)
                    ],
                ];
            }
        }

        // Output the grouped data
        return $groupedData;
    }

    private function getRouteTotalsForIR($groupedRecords)
    {
        $payModes = ['Cash & Carry', 'Bill to Bill', 'Weekly', 'Twice Monthly', 'Monthly'];
        $totals = [
            'qty' => 0,
            'amount' => 0
        ];

        foreach ($payModes as $payMode) {
            if (isset($groupedRecords[$payMode])) {
                $record = $groupedRecords[$payMode];
                $totals['qty'] += (float)($record['totals']['qty'] ?? 0);
                $totals['amount'] += (float)($record['totals']['amount'] ?? 0);
            }
        }

        $totals['qty']    = getTwoDigitPrecision($totals['qty']);
        $totals['amount'] = getTwoDigitPrecision($totals['amount']);

        return $totals;
    }

    private function getInvoiceReportTotals($records)
    {
        $totals = [
            'qty'    => 0,
            'amount' => 0
        ];

        foreach ($records as $record) {
            $record = $record['routeTotals'];
            if($record) {
                $totals['qty']    += (float)($record['qty'] ?? 0);
                $totals['amount'] += (float)($record['amount'] ?? 0);
            }
        }
        
        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumberWithDecimal($value);
        }

        return $totals;
    }

    private function getRouteRecordsForIR($reportData)
    {
        $records = [];
        foreach($reportData as $data) {
            $hasData = (float) ($data['routeTotals']['qty']) && (float) $data['routeTotals']['amount'];
            if($hasData) {
                $record['route']  = $data['route'];
                $record['count']  = '';
                $record['qty']    = $data['routeTotals']['qty'];
                $record['amount'] = $data['routeTotals']['amount'];

                // Initialize count
                $count = 0;
                // Loop through each pay mode and accumulate the record counts
                foreach ($data['routeRecords'] as $payMode) {
                    $count += (float) (count($payMode['records']));
                }
                $record['count']  = $count;

                $records[] = $record;
            }
        }
        return $records;
    }

    private function getPaymentModeRecordsForIR($reportData)
    {
        $payModeTotals = [];

        foreach ($reportData as $routeData) {
            foreach ($routeData['routeRecords'] as $payMode => $data) {
                // Initialize the pay_mode if it doesn't exist
                if (!isset($payModeTotals[$payMode])) {
                    $payModeTotals[$payMode] = [
                        'count'  => 0,
                        'qty'    => 0,
                        'amount' => 0
                    ];
                }

                // Increment the count for each record
                $payModeTotals[$payMode]['count']++;
                // Add up the totals for each pay_mode
                $payModeTotals[$payMode]['qty']    += $data['totals']['qty'];
                $payModeTotals[$payMode]['amount'] += $data['totals']['amount'];
            }
        }

        // Round Off
        $payModes = ['Cash & Carry', 'Bill to Bill', 'Weekly', 'Twice Monthly', 'Monthly'];
        foreach($payModes as $payMode) {
            if (isset($payModeTotals[$payMode])) {
                $payModeTotals[$payMode]['qty']    = getTwoDigitPrecision($payModeTotals[$payMode]['qty']);
                $payModeTotals[$payMode]['amount'] = getTwoDigitPrecision($payModeTotals[$payMode]['amount']);
            }
        }

        $payModeRecords = [];

        foreach ($payModeTotals as $pay_mode => $data) {
            // Add each pay_mode with its corresponding data to the new structure
            $payModeRecords[] = array_merge(['pay_mode' => $pay_mode], $data);
        }

        return $payModeRecords;
    }
/* Invoice Report - Section End */


/* Day Wise Report - Section Start */    
    public function dayWiseReport(Request $request, DayWiseReportService $service)
    {
        $fromDate   = $request->input('fromDate', Carbon::today()->toDateString());
        $toDate     = $request->input('toDate', Carbon::today()->toDateString());
        $routeId    = $request->input('route', 0);
        $reportType = $request->input('reportType', "Format1");
        $yestBal    = $request->has('yest_bal');

        $result = $service->getReport([
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
            'routeId'    => $routeId,
            'reportType' => $reportType,
        ]);

        // return response()->json([
        return view('reports.daywise-report', [
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'reportType'     => $reportType,
            'routeId'        => $routeId,
            'routes'         => app(\App\Repositories\Reports\DayWiseReportRepository::class)->getRoutes(),
            'banks'          => $result['banks'],
            'reportData'     => $result['reportData'],
            'grandTotals'    => $result['grandTotals'],
            'payModeRecords' => $result['payModeRecords'],
            'yest_bal'       => $yestBal,
        ]);
    }

    public function dayWiseReport_old(Request $request)
    {
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $reportType = $request->input('reportType') ?? "Format1";
        $routeId = $request->input('route') ?? 0;
        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $banks = BankMaster::select('id','display_name')->orderBy('id')->get();
        $showYestBal = (bool) $request->input('yest_bal', true);

        $data = [];
        $totals = [];
        $payModeRecords = [];
        if($routeId) {
            $data[] = $this->getDayWiseData([$fromDate, $toDate], $routeId, $reportType);
        }
        else {
            $data[] = $this->getDayWiseData([$fromDate, $toDate], -1, $reportType);
            $data[] = $this->getDayWiseData([$fromDate, $toDate], -2, $reportType);
            foreach($routes as $route) {
                $data[] = $this->getDayWiseData([$fromDate, $toDate], $route->id, $reportType);
            }
            
            if($reportType == "Format1") {
                $totals = $this->calculateDayWiseTotalsFormat1($data);
                $payModeRecords = $this->getDayWisePaymentModeRecordsFormat1($data);
            }
            else {
                $totals = $this->calculateDayWiseTotalsFormat2($data, $banks);
                $payModeRecords = $this->getDayWisePaymentModeRecordsFormat2($data, $banks);
            }
        }

        // return response()->json([
        return view('reports.daywise-report', [
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'reportType'     => $reportType,
            'routeId'        => $routeId,
            'routes'         => $routes,
            'banks'          => $banks,
            'reportData'     => $data,
            'grandTotals'    => $totals,
            'payModeRecords' => $payModeRecords,
            'yest_bal'       => $showYestBal,
        ]);
    }

    private function getDayWiseData($dates, $routeId, $reportType) {
        // Determine routeName and customer group based on routeId
        if ($routeId == -1) {
            $routeName = "Company";
            $customerGroup = 'Company';
        } 
        elseif ($routeId == -2) {
            $routeName = "Function";
            $customerGroup = 'Function';
        } 
        else {
            $routeName = strtoupper(MRoute::where('id', $routeId)->value('name'));
            $customerGroup = ['Retailer', 'Distributor', 'Outlet'];
        }

        if($routeId == -1 || $routeId == -2) {
            $customers = Customer::select('id', 'customer_name', 'payment_mode')
                ->where('group', $customerGroup)
                ->orderByRaw("FIELD(payment_mode, 'Cash & Carry', 'Bill to Bill', 'Weekly', 'Twice Monthly', 'Monthly')")
                ->orderBy('customer_name')
                ->get();
        }        
        else {
            $customers = Customer::select('id', 'customer_name', 'payment_mode')
                ->where('route_id', $routeId)
                ->whereIn('group', $customerGroup)
                ->orderByRaw("FIELD(payment_mode, 'Cash & Carry', 'Bill to Bill', 'Weekly', 'Twice Monthly', 'Monthly')")
                ->orderBy('customer_name')
                ->get();
        }

        if($reportType == "Format1")
            $records =  $this->getDayWiseRecordsFormat1($customers, $dates);
        else
            $records =  $this->getDayWiseRecordsFormat2($customers, $dates);

        $data = array_merge(['route' => $routeName], $records);

        return $data; 
    }

    private function getDayWiseRecordsFormat1($customers, $dates) {
        $groupedRecords = [];
        $routeTotals = [
            'open_bal'  => 0,
            'inv_amt'   => 0,
            'prev_inv'  => 0,
            'cash'      => 0,
            'bank'      => 0,
            'incentive' => 0,
            'deposit'   => 0,
            'others'    => 0,
            'day_bal'   => 0,
            'yest_bal'  => 0,
            'close_bal' => 0,
        ];

        foreach ($customers as $customer) {
            $record = [];
            $record['customer']  = $customer->customer_name;
            $record['open_bal']  = $this->getOpeningBalance($customer->id, $dates[0]);
            $record['inv_amt']   = $this->getInvoiceAmount($customer->id, $dates);
            $record['prev_inv']  = $this->getPreviousInvoiceAmount($customer->id, $dates[0]);
            $record['cash']      = $this->getReceiptAmount($customer->id, $dates, 'Cash');
            $record['bank']      = $this->getReceiptAmount($customer->id, $dates, 'Bank');
            $record['incentive'] = $this->getReceiptAmount($customer->id, $dates, 'Incentive');
            $record['deposit']   = $this->getReceiptAmount($customer->id, $dates, 'Deposit');
            $record['others']    = $this->getReturnAmount($customer->id, $dates);            
            $record['day_bal']   = $record['inv_amt'] - $record['cash'] - $record['bank'] - $record['incentive'] - $record['deposit'] - $record['others'];            
            $record['close_bal'] = $record['open_bal'] + $record['inv_amt'] - $record['cash'] - $record['bank'] - $record['incentive'] - $record['deposit'] - $record['others'];
            if($record['prev_inv'])
                $record['yest_bal'] = $record['prev_inv'] - $record['cash'] - $record['bank'] - $record['incentive'] - $record['deposit'] - $record['others'];
            else
                $record['yest_bal'] = 0;

            if ($record['open_bal'] || $record['inv_amt'] || $record['cash'] || $record['bank'] || $record['incentive'] || $record['deposit'] || $record['others']) {
                // Group by payment mode
                if (!isset($groupedRecords[$customer->payment_mode])) {
                    $groupedRecords[$customer->payment_mode] = [
                        'records' => [],
                        'totals' => [
                            'open_bal'  => 0,
                            'inv_amt'   => 0,
                            'prev_inv'  => 0,
                            'cash'      => 0,
                            'bank'      => 0,
                            'incentive' => 0,
                            'deposit'   => 0,
                            'others'    => 0,
                            'day_bal'   => 0,
                            'yest_bal'  => 0,
                            'close_bal' => 0,
                        ]
                    ];
                }
                
                // Update payment mode group totals
                $groupedRecords[$customer->payment_mode]['totals']['open_bal']  += $record['open_bal'];
                $groupedRecords[$customer->payment_mode]['totals']['inv_amt']   += $record['inv_amt'];
                $groupedRecords[$customer->payment_mode]['totals']['prev_inv']  += $record['prev_inv'];
                $groupedRecords[$customer->payment_mode]['totals']['cash']      += $record['cash'];
                $groupedRecords[$customer->payment_mode]['totals']['bank']      += $record['bank'];
                $groupedRecords[$customer->payment_mode]['totals']['incentive'] += $record['incentive'];
                $groupedRecords[$customer->payment_mode]['totals']['deposit']   += $record['deposit'];
                $groupedRecords[$customer->payment_mode]['totals']['others']    += $record['others'];
                $groupedRecords[$customer->payment_mode]['totals']['day_bal']   += $record['day_bal'];
                $groupedRecords[$customer->payment_mode]['totals']['yest_bal']  += $record['yest_bal'];
                $groupedRecords[$customer->payment_mode]['totals']['close_bal'] += $record['close_bal'];

                // Update route totals
                $routeTotals['open_bal']  += $record['open_bal'];
                $routeTotals['inv_amt']   += $record['inv_amt'];
                $routeTotals['prev_inv']  += $record['prev_inv'];
                $routeTotals['cash']      += $record['cash'];
                $routeTotals['bank']      += $record['bank'];
                $routeTotals['incentive'] += $record['incentive'];
                $routeTotals['deposit']   += $record['deposit'];
                $routeTotals['others']    += $record['others'];
                $routeTotals['day_bal']   += $record['day_bal'];
                $routeTotals['yest_bal']  += $record['yest_bal'];
                $routeTotals['close_bal'] += $record['close_bal'];

                // Empty if amount as zero
                if($record['open_bal']  == 0) $record['open_bal']  = "";
                if($record['inv_amt']   == 0) $record['inv_amt']   = "";
                if($record['prev_inv']  == 0) $record['prev_inv']   = "";
                if($record['cash']      == 0) $record['cash']      = "";
                if($record['bank']      == 0) $record['bank']      = "";
                if($record['incentive'] == 0) $record['incentive'] = "";
                if($record['deposit']   == 0) $record['deposit']   = "";
                if($record['others']    == 0) $record['others']    = "";
                if($record['day_bal']   == 0) $record['day_bal']    = "";
                if($record['yest_bal']  == 0) $record['yest_bal']    = "";
                if($record['close_bal'] == 0) $record['close_bal'] = "";

                // Push record to the respective payment mode group
                $groupedRecords[$customer->payment_mode]['records'][] = $record;
            }            
        }

        return [
            'routeRecords' => $groupedRecords,
            'routeTotals'  => $routeTotals,
        ];
    }

    private function getDayWiseRecordsFormat2($customers, $dates) {
        $banks = BankMaster::select('id','display_name')->orderBy('id')->get();

        $groupedRecords = [];
        $routeTotals = [
            'inv_amt'   => 0,
            'prev_inv'  => 0,
            'cash'      => 0,
            'incentive' => 0,
            'deposit'   => 0,
            'others'    => 0,
            'day_bal'   => 0,
            'yest_bal'  => 0,
        ];
    
        // Initialize totals for each bank
        foreach ($banks as $bank) {
            $routeTotals[$bank->display_name] = 0;
        }
    
        foreach ($customers as $customer) {
            $record = [];
            $record['customer']  = $customer->customer_name;
            $record['inv_amt']   = $this->getInvoiceAmount($customer->id, $dates);
            $record['prev_inv']  = $this->getPreviousInvoiceAmount($customer->id, $dates[0]);
            $record['cash']      = $this->getReceiptAmount($customer->id, $dates, 'Cash');
            $record['incentive'] = $this->getReceiptAmount($customer->id, $dates, 'Incentive');
            $record['deposit']   = $this->getReceiptAmount($customer->id, $dates, 'Deposit');
            $record['others']    = $this->getReturnAmount($customer->id, $dates);
            $record['day_bal']   = $record['inv_amt'] - $record['cash'] - $record['incentive'] - $record['deposit'] - $record['others'];
            if($record['prev_inv'])
                $record['yest_bal'] = $record['prev_inv'] - $record['cash'] - $record['incentive'] - $record['deposit'] - $record['others'];
            else
                $record['yest_bal'] = 0;
    
            // Dynamically adding bank-related attributes
            foreach ($banks as $bank) {
                $rcptAmt = $this->getReceiptAmount($customer->id, $dates, 'Bank', $bank->id);
                $record[$bank->display_name] = $rcptAmt;
                $record['day_bal'] -= $rcptAmt;
            }

            $isEmpty = true;
            if ($record['inv_amt'] || $record['cash'] || $record['incentive'] || $record['deposit'] || $record['others'] || $record['prev_inv'] || $record['yest_bal']) {
                $isEmpty = false;
            }
            else {
                foreach ($banks as $bank) {
                     if($record[$bank->display_name]) {
                        $isEmpty = false;
                        break;
                     }
                }
            }

            if(!$isEmpty) {
                // Group by payment mode
                if (!isset($groupedRecords[$customer->payment_mode])) {
                    $groupedRecords[$customer->payment_mode] = [
                        'records' => [],
                        'totals' => [
                            'inv_amt'   => 0,
                            'prev_inv'  => 0,
                            'cash'      => 0,
                            'incentive' => 0,
                            'deposit'   => 0,                            
                            'others'    => 0,
                            'day_bal'   => 0,
                            'yest_bal'  => 0,
                        ]
                    ];
        
                    // Initialize totals for each bank
                    foreach ($banks as $bank) {
                        $groupedRecords[$customer->payment_mode]['totals'][$bank->display_name] = 0;
                    }
                }
        
                // Update payment mode group totals
                $groupedRecords[$customer->payment_mode]['totals']['inv_amt']   += $record['inv_amt'];
                $groupedRecords[$customer->payment_mode]['totals']['prev_inv']  += $record['prev_inv'];
                $groupedRecords[$customer->payment_mode]['totals']['cash']      += $record['cash'];
                $groupedRecords[$customer->payment_mode]['totals']['incentive'] += $record['incentive'];
                $groupedRecords[$customer->payment_mode]['totals']['deposit']   += $record['deposit'];
                $groupedRecords[$customer->payment_mode]['totals']['others']    += $record['others'];
                $groupedRecords[$customer->payment_mode]['totals']['day_bal']   += $record['day_bal'];
                $groupedRecords[$customer->payment_mode]['totals']['yest_bal']  += $record['yest_bal'];

                // Update bank totals for each bank
                foreach ($banks as $bank) {
                    $groupedRecords[$customer->payment_mode]['totals'][$bank->display_name] += $record[$bank->display_name];
                    $routeTotals[$bank->display_name] += $record[$bank->display_name];
                }
        
                // Update route totals
                $routeTotals['inv_amt']   += $record['inv_amt'];
                $routeTotals['prev_inv']  += $record['prev_inv'];
                $routeTotals['cash']      += $record['cash'];
                $routeTotals['incentive'] += $record['incentive'];
                $routeTotals['deposit']   += $record['deposit'];
                $routeTotals['others']    += $record['others'];
                $routeTotals['day_bal']   += $record['day_bal'];
                $routeTotals['yest_bal']  += $record['yest_bal'];

                // Empty if amount is zero
                if ($record['inv_amt'] == 0)   $record['inv_amt']   = "";
                if ($record['prev_inv'] == 0)  $record['prev_inv']  = "";
                if ($record['cash'] == 0)      $record['cash']      = "";
                if ($record['incentive'] == 0) $record['incentive'] = "";
                if ($record['deposit'] == 0)   $record['deposit']   = "";
                if ($record['others'] == 0)    $record['others']    = "";
                if ($record['day_bal'] == 0)   $record['day_bal']   = "";
                if ($record['yest_bal'] == 0)  $record['yest_bal']   = "";
                foreach ($banks as $bank) {
                    if ($record[$bank->display_name] == 0) $record[$bank->display_name] = "";
                }
        
                // Push record to the respective payment mode group
                $groupedRecords[$customer->payment_mode]['records'][] = $record;
            }
        }
    
        return [
            'routeRecords' => $groupedRecords,
            'routeTotals' => $routeTotals,
        ];
    }

    private function getInvoiceAmount($customerId, $dates) {        
        $isSingleDate = is_string($dates) || count($dates) === 1;
    
        $queryModifier = fn($query) => $isSingleDate
            ? $query->where('invoice_date', $dates)
            : $query->whereBetween('invoice_date', $dates);
    
        $salInvAmt  = SalesInvoice::where('customer_id', $customerId)->where('invoice_status','<>','Cancelled')->tap($queryModifier)->sum('net_amt');
        $taxInvAmt  = TaxInvoice::where('customer_id', $customerId)->where('invoice_status','<>','Cancelled')->tap($queryModifier)->sum('net_amt');
        $bulkInvAmt = BulkMilkOrder::where('customer_id', $customerId)->where('invoice_status','<>','Cancelled')->tap($queryModifier)->sum('net_amt');
    
        return $salInvAmt + $taxInvAmt + $bulkInvAmt;
    }

    private function getReceiptAmount($customerId, $dates, $mode, $bankId = null) {
        $isSingleDate = is_string($dates) || count($dates) === 1;

        $query = Receipt::where('customer_id', $customerId)
            ->where('status', 'Approved')
            ->where('mode', $mode)
            ->when($isSingleDate, fn($q) => $q->where('receipt_date', $dates),
                                fn($q) => $q->whereBetween('receipt_date', $dates))
            ->when($mode === 'Bank' && $bankId, fn($q) => $q->where('bank_id', $bankId));
        
        return $query->sum('amount');
    }

    private function getReturnAmount($customerId, $dates) {        
        $isSingleDate = is_string($dates) || count($dates) === 1;
    
        $queryModifier = fn($query) => $isSingleDate
            ? $query->where('txn_date', $dates)
            : $query->whereBetween('txn_date', $dates);
    
        $amount = SalesReturn::where('customer_id', $customerId)
            ->tap($queryModifier)
            ->sum('net_amt');
    
        return $amount;
    }

    private function getCreditNoteAmount($customerId, $dates) {        
        $isSingleDate = is_string($dates) || count($dates) === 1;
    
        $queryModifier = fn($query) => $isSingleDate
            ? $query->where('document_date', $dates)
            : $query->whereBetween('document_date', $dates);
    
        $amount = CreditNote::where('customer_id', $customerId)
            ->tap($queryModifier)
            ->where('status',DocumentStatus::APPROVED)
            ->sum('amount');
    
        return $amount;
    }
    
    private function calculateDayWiseTotalsFormat1($records)
    {
        $totals = [
            'open_bal'  => 0,
            'inv_amt'   => 0,
            'prev_inv'  => 0,
            'cash'      => 0,
            'bank'      => 0,
            'incentive' => 0,
            'deposit'   => 0,
            'others'    => 0,
            'day_bal'   => 0,
            'yest_bal'  => 0,
            'close_bal' => 0
        ];

        foreach ($records as $record) {
            $record = $record['routeTotals'];
            if($record) {
                $totals['open_bal']  += (float)($record['open_bal'] ?? 0);
                $totals['inv_amt']   += (float)($record['inv_amt'] ?? 0);
                $totals['prev_inv']  += (float)($record['prev_inv'] ?? 0);
                $totals['cash']      += (float)($record['cash'] ?? 0);
                $totals['bank']      += (float)($record['bank'] ?? 0);
                $totals['incentive'] += (float)($record['incentive'] ?? 0);
                $totals['deposit']   += (float)($record['deposit'] ?? 0);
                $totals['others']    += (float)($record['others'] ?? 0);
                $totals['day_bal']   += (float)($record['day_bal'] ?? 0);
                $totals['yest_bal']  += (float)($record['yest_bal'] ?? 0);
                $totals['close_bal'] += (float)($record['close_bal'] ?? 0);
            }
        }

        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumber($value);
        }

        return $totals;
    }

    private function getDayWisePaymentModeRecordsFormat1($reportData)
    {
        $payModeTotals = [];

        foreach ($reportData as $routeData) {
            foreach ($routeData['routeRecords'] as $payMode => $data) {
                // Initialize the pay_mode if it doesn't exist
                if (!isset($payModeTotals[$payMode])) {
                    $payModeTotals[$payMode] = [
                        'open_bal'  => 0,
                        'inv_amt'   => 0,
                        'prev_inv'  => 0,
                        'cash'      => 0,
                        'bank'      => 0,
                        'incentive' => 0,
                        'deposit'   => 0,
                        'others'    => 0,
                        'day_bal'   => 0,
                        'yest_bal'  => 0,
                        'close_bal' => 0
                    ];
                }

                // Add up the totals for each pay_mode
                $payModeTotals[$payMode]['open_bal']  += $data['totals']['open_bal'];
                $payModeTotals[$payMode]['inv_amt']   += $data['totals']['inv_amt'];
                $payModeTotals[$payMode]['prev_inv']  += $data['totals']['prev_inv'];
                $payModeTotals[$payMode]['cash']      += $data['totals']['cash'];
                $payModeTotals[$payMode]['bank']      += $data['totals']['bank'];
                $payModeTotals[$payMode]['incentive'] += $data['totals']['incentive'];
                $payModeTotals[$payMode]['deposit']   += $data['totals']['deposit'];
                $payModeTotals[$payMode]['others']    += $data['totals']['others'];
                $payModeTotals[$payMode]['day_bal']   += $data['totals']['day_bal'];
                $payModeTotals[$payMode]['yest_bal']  += $data['totals']['yest_bal'];
                $payModeTotals[$payMode]['close_bal'] += $data['totals']['close_bal'];
            }
        }

        $payModeRecords = [];

        foreach ($payModeTotals as $pay_mode => $data) {
            // Add each pay_mode with its corresponding data to the new structure
            $payModeRecords[] = array_merge(['pay_mode' => $pay_mode], $data);
        }

        return $payModeRecords;
    }

    private function calculateDayWiseTotalsFormat2($records, $banks)
    {
        // Initialize totals array
        $totals = [
            'inv_amt'   => 0,
            'prev_inv'  => 0,
            'cash'      => 0,
            'incentive' => 0,
            'deposit'   => 0,
            'others'    => 0,
            'day_bal'   => 0,
            'yest_bal'  => 0,
        ];

        // Initialize totals for each bank
        foreach ($banks as $bank) {
            $totals[$bank->display_name] = 0;
        }

        // Iterate through records to calculate totals
        foreach ($records as $record) {
            $record = $record['routeTotals']; // Assuming 'routeTotals' contains the data
            if ($record) {
                $totals['inv_amt']   += (float)($record['inv_amt'] ?? 0);
                $totals['prev_inv']  += (float)($record['prev_inv'] ?? 0);
                $totals['cash']      += (float)($record['cash'] ?? 0);                
                $totals['incentive'] += (float)($record['incentive'] ?? 0);
                $totals['deposit']   += (float)($record['deposit'] ?? 0);
                $totals['others']    += (float)($record['others'] ?? 0);
                $totals['day_bal']   += (float)($record['day_bal'] ?? 0);
                $totals['yest_bal']  += (float)($record['yest_bal'] ?? 0);

                // Add totals for each bank
                foreach ($banks as $bank) {
                    $totals[$bank->display_name] += (float)($record[$bank->display_name] ?? 0);
                }
            }
        }

        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumber($value);
        }

        return $totals;
    }

    private function getDayWisePaymentModeRecordsFormat2($reportData, $banks)
    {
        $payModeTotals = [];

        foreach ($reportData as $routeData) {
            foreach ($routeData['routeRecords'] as $payMode => $data) {
                // Initialize the pay_mode if it doesn't exist
                if (!isset($payModeTotals[$payMode])) {
                    $payModeTotals[$payMode] = [
                        'inv_amt'   => 0,
                        'cash'      => 0,
                        'prev_inv'  => 0,
                        'incentive' => 0,
                        'deposit'   => 0,
                        'others'    => 0,
                        'day_bal'   => 0,
                        'yest_bal'  => 0,
                    ];

                    // Initialize totals for each bank dynamically
                    foreach ($banks as $bank) {
                        $payModeTotals[$payMode][$bank->display_name] = 0;
                    }
                }

                // Add up the totals for each pay_mode
                $payModeTotals[$payMode]['inv_amt']   += $data['totals']['inv_amt'];
                $payModeTotals[$payMode]['prev_inv']  += $data['totals']['prev_inv'];
                $payModeTotals[$payMode]['cash']      += $data['totals']['cash'];
                $payModeTotals[$payMode]['incentive'] += $data['totals']['incentive'];
                $payModeTotals[$payMode]['deposit']   += $data['totals']['deposit'];
                $payModeTotals[$payMode]['others']    += $data['totals']['others'];
                $payModeTotals[$payMode]['day_bal']   += $data['totals']['day_bal'];
                $payModeTotals[$payMode]['yest_bal']  += $data['totals']['yest_bal'];

                // Add up the totals for each bank dynamically
                foreach ($banks as $bank) {
                    $payModeTotals[$payMode][$bank->display_name] += $data['totals'][$bank->display_name] ?? 0;
                }
            }
        }

        $payModeRecords = [];

        foreach ($payModeTotals as $pay_mode => $data) {
            // Add each pay_mode with its corresponding data to the new structure
            $payModeRecords[] = array_merge(['pay_mode' => $pay_mode], $data);
        }

        return $payModeRecords;
    }
/* Day Wise Report - Section End */


/* Customer Report - Section Start */
    public function accountStyleCustomerReport(Request $request)
    {
        $customerId = $request->input('customer_id', 0);
        $fromDate   = $request->input('from_date', date('Y-m-d'));
        $toDate     = $request->input('to_date', $fromDate);

        $customers = Customer::select('id', 'customer_name')
            ->where('status', 'Active')
            ->orderBy('customer_name')
            ->get();

        $data = $customerId ? $this->getAccountStyleCustomerData($customerId, $fromDate, $toDate) : [];
        $banks = BankMaster::select('id','bank_name','acc_number','branch','ifsc')->get();

        return view('reports.customers.customer-account-report', [
            'dates'     => ['from' => $fromDate, 'to' => $toDate],            
            'customer'  => $data['customer'] ?? null, // Use array notation
            'balances'  => $data['balances'] ?? [],
            'records'   => $data['records'] ?? [],
            'totals'    => $data['totals'] ?? [],
            'customers' => $customers,
            'banks'     => $banks,
        ]);

    }

    public function getAccountStyleCustomerData($customerId, $fromDate, $toDate)
    {
        $records = $totals = $balances = [];
        $customer = Customer::select('id','customer_name','address_lines')->where('id', $customerId)->first();
        $banks = BankMaster::select('id','display_name')->orderBy('id')->get();
        $dates = [$fromDate, $toDate];

        $this->generateInvoiceRecordsForASCR($records, $dates, $customerId, SalesInvoice::class, 'Sales Invoice');
        $this->generateInvoiceRecordsForASCR($records, $dates, $customerId, TaxInvoice::class, 'Tax Invoice');
        $this->generateInvoiceRecordsForASCR($records, $dates, $customerId, BulkMilkOrder::class, 'Bulk Milk Order');
        $this->generateReceiptRecordsForASCR($records, $dates, $customerId, 'Cash');

        foreach ($banks as $bank) {
            $this->generateReceiptRecordsForASCR($records, $dates, $customerId, 'Bank', $bank);
        }
        
        $this->generateReceiptRecordsForASCR($records, $dates, $customerId, 'Incentive');

        foreach ($banks as $bank) {
            $this->generateReceiptRecordsForASCR($records, $dates, $customerId, 'Deposit', $bank);
        }

        $this->generateSalesReturnRecordsForASCR($records, $dates, $customerId);

        $this->generateCreditNoteRecordsForASCR($records, $dates, $customerId);

        // Sort records
        usort($records, function ($a, $b) {
            $dateComparison = strcmp($a['date'], $b['date']);
            return $dateComparison == 0 ? strcmp($a['created_at'], $b['created_at']) : $dateComparison;
        });

        // Calculate totals
        $totals = ['debit' => 0, 'credit' => 0];

        foreach ($records as $record) {
            $totals['debit'] += (float)($record['debit'] ?? 0);
            $totals['credit'] += (float)($record['credit'] ?? 0);
        }

        // Format records
        foreach ($records as &$record) {
            $record['date'] = formatDateToDMY($record['date']);
            if ($record['debit']) {
                $record['debit'] = formatIndianNumberWithDecimal($record['debit']);
                $record['particulars'] = "Cr    {$record['particulars']}";
            } elseif ($record['credit']) {
                $record['credit'] = formatIndianNumberWithDecimal($record['credit']);
                $record['particulars'] = "Dr    {$record['particulars']}";
            }
        }

        // Opening balance
        $openingAmount = $this->getOpeningBalance($customerId, $fromDate);
        $openingBalance['date'] = formatDateToDMY($fromDate);
        if ($openingAmount >= 0) {
            $openingBalance['debit'] = formatIndianNumberWithDecimal($openingAmount);
            $openingBalance['credit'] = "";
            $totals['debit'] += $openingAmount;
        } else {
            $openingBalance['debit'] = "";
            $openingBalance['credit'] = formatIndianNumberWithDecimal(abs($openingAmount));
            $totals['credit'] += $openingAmount;
        }
        $balances['Opening Balance'] = $openingBalance;

        // Closing balance
        $closingAmount = $totals['debit'] - $totals['credit'];
        $closingBalance['date'] = formatDateToDMY($toDate);
        $closingBalance['debit'] = $closingAmount < 0 ? formatIndianNumberWithDecimal(abs($closingAmount)) : "";
        $closingBalance['credit'] = $closingAmount >= 0 ? formatIndianNumberWithDecimal($closingAmount) : "";
        $balances['Closing Balance'] = $closingBalance;

        $totals['total'] = max($totals['debit'], $totals['credit']);

        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumberWithDecimal($value);
        }

        return compact('customer', 'balances', 'records', 'totals');
    }

    private function generateInvoiceRecordsForASCR(&$records, $dates, $customerId, $model, $type) // ASCR - Account Style Customer Report
    {
        // Fetch the invoices
        $invoices = $model::select('id', 'invoice_num', 'invoice_date', 'net_amt', 'created_at')
            ->whereBetween('invoice_date', $dates)
            ->where('customer_id', $customerId)
            ->where('invoice_status','<>','Cancelled')
            ->get();

        foreach ($invoices as $invoice) {
            // Create the record array
            $record['date']        = $invoice->invoice_date;
            $record['particulars'] = $type;
            $record['vtype']       = 'Sales';
            $record['vnum']        = $invoice->invoice_num;
            $record['debit']       = getTwoDigitPrecision($invoice->net_amt);
            $record['credit']      = "";
            $record['created_at']  = $invoice->created_at;
            // Add the record to the records array
            $records[] = $record;
        }
    }

    private function generateReceiptRecordsForASCR(&$records, $dates, $customerId, $mode, $bank = null)
    {
        $query = Receipt::select('id', 'receipt_num', 'receipt_date', 'amount', 'created_at')
            ->where('customer_id', $customerId)
            ->whereBetween('receipt_date', $dates)
            ->where('status', 'Approved')
            ->where('mode', $mode);

        $particulars = $mode;
        if ($mode === 'Bank' || $mode === "Deposit" && $bank) {
            $query->where('bank_id', $bank->id);
            $particulars = "{$mode} [{$bank->display_name}]";
        }

        $receipts = $query->get();        

        foreach ($receipts as $receipt) {
            $record = [
                'date'        => $receipt->receipt_date,
                'particulars' => $particulars,
                'vtype'       => "Receipt",
                'vnum'        => $receipt->receipt_num,
                'debit'       => "",
                'credit'      => getTwoDigitPrecision($receipt->amount),
                'created_at'  => $receipt->created_at,
            ];
            $records[] = $record;
        }
    }    

    private function generateSalesReturnRecordsForASCR(&$records, $dates, $customerId)
    {
        $salesReturns = SalesReturn::select('id','txn_id','txn_date','invoice_num','net_amt', 'created_at')
            ->whereBetween('txn_date', $dates)
            ->where('customer_id', $customerId)
            ->get();

        foreach($salesReturns as $salesReturn) {
            $record['date']        = $salesReturn->txn_date;
            $record['particulars'] = "Sales Return";
            $record['vtype']       = "Sales Return";
            $record['vnum']        = $salesReturn->txn_id;
            $record['debit']       = "";
            $record['credit']      = getTwoDigitPrecision($salesReturn->net_amt);
            $record['created_at']  = $salesReturn->created_at;
            $records[] = $record;
        }
    }

    private function generateCreditNoteRecordsForASCR(&$records, $dates, $customerId)
    {
        $creditNotes = CreditNote::select('id','document_number','document_date','amount','created_at')
            ->whereBetween('document_date', $dates)
            ->where('customer_id', $customerId)
            ->where('status', DocumentStatus::APPROVED)
            ->get();

        foreach($creditNotes as $creditNote) {
            $record['date']        = $creditNote->document_date;
            $record['particulars'] = "Credit Note";
            $record['vtype']       = "Credit Note";
            $record['vnum']        = $creditNote->document_number;
            $record['debit']       = "";
            $record['credit']      = getTwoDigitPrecision($creditNote->amount);
            $record['created_at']  = $creditNote->created_at;
            $records[] = $record;
        }
    }

    public function statementStyleCustomerReport(Request $request)
    {        
        $customerId = $request->input('customer_id', 0);
        $fromDate   = $request->input('from_date', date('Y-m-d'));
        $toDate     = $request->input('to_date', $fromDate);

        $customers = Customer::select('id', 'customer_name')
            ->where('status', 'Active')
            ->orderBy('customer_name')
            ->get();

        $data = $this->getCustomerReportData($customerId, $fromDate, $toDate);
        $banks = BankMaster::select('id','bank_name','acc_number','branch','ifsc')->get();

        return view('reports.customers.customer-statement-report', [
            'dates'     => ['from' => $fromDate, 'to' => $toDate],
            'customer'  => $data['customer'],
            'records'   => $data['records'],
            'totals'    => $data['totals'],
            'summary'   => $data['summary'],
            'customers' => $customers,
            'banks'     => $banks,
        ]);
    }

    public function getCustomerReportData($customerId, $fromDate, $toDate)
    {
        $customerJson = "";
        $records = $summary = [];
        $totals = array_fill_keys(['qty', 'invoice', 'cash', 'bank', 'incentive', 'deposit', 'returns', 'discount'], 0);

        if($customerId) {
            $customer = Customer::select('id', 'customer_name', 'route_id')
                ->with('route:id,name')
                ->where('id', $customerId)
                ->first(); 

            $openingBalance = $this->getOpeningBalance($customerId, $fromDate);
            $closingBalance = $openingBalance;            
            $dates = getDateRange($fromDate, $toDate);            

            foreach ($dates as $dat) {
                $date         = $dat->format('Y-m-d');
                $invoices     = $this->getInvoiceDataForSSCR($customerId, $date);
                $invoiceQty   = $invoices->sum('qty');                  
                $invoiceTotal = $invoices->sum('net_amt');
                $cash         = $this->getReceiptAmount($customerId, $date, 'Cash');
                $bank         = $this->getReceiptAmount($customerId, $date, 'Bank');
                $incentive    = $this->getReceiptAmount($customerId, $date, 'Incentive');
                $deposit      = $this->getReceiptAmount($customerId, $date, 'Deposit');
                $returns      = $this->getReturnAmount($customerId, $date);
                $creditNotes  = $this->getCreditNoteAmount($customerId, $date);
                $bankRecords  = $this->getReceiptAmountWithBank($customerId, $date);

                $inward = $cash + $bank + $incentive + $deposit + $returns + $creditNotes;                
                if($invoiceTotal > 0 || $inward > 0) {
                    $closingBalance = $closingBalance + $invoiceTotal - $inward;

                    $records[] = [
                        'date'         => $dat->format('d-m-Y'),
                        'invoices'     => $invoices->toArray(),                         
                        'invoiceQty'   => $invoiceQty,
                        'invoiceTotal' => $invoiceTotal,
                        'cash'         => $cash,
                        'bank'         => $bank,
                        'bankRecords'  => $bankRecords,
                        'incentive'    => $incentive,
                        'deposit'      => $deposit,
                        'returns'      => $returns,
                        'discount'     => $creditNotes,
                        'balance'      => $closingBalance,
                    ];

                    $totals['qty']       += $invoiceQty;
                    $totals['invoice']   += $invoiceTotal;
                    $totals['cash']      += $cash;
                    $totals['bank']      += $bank;
                    $totals['incentive'] += $incentive;
                    $totals['deposit']   += $deposit;
                    $totals['returns']   += $returns;
                    $totals['discount']  += $creditNotes;
                }
            }

            $summary['opening']  = $openingBalance;
            $summary['invoices'] = $totals['invoice'];
            $summary['receipts'] = $totals['cash'] + $totals['bank'] + $totals['incentive'] + $totals['deposit'];
            $summary['returns']  = $totals['returns'];
            $summary['discount'] = $totals['discount'];
            $summary['closing']  = $closingBalance;

            $customerJson = [
                'id' => $customer->id,
                'name' => $customer->customer_name,
                'route' => $customer->route->name,
            ];
        }

        return [
            'customer'  => $customerJson,
            'records'   => $records,
            'totals'    => $totals,
            'summary'   => $summary,
        ];
    }

    private function getInvoiceDataForSSCR($customerId, $date) { // Statement Style Customer Report
        $salesInvoices = SalesInvoice::where('invoice_date', $date)
            ->where('customer_id', $customerId)
            ->where('invoice_status','<>','Cancelled')
            ->get(['invoice_num','qty','net_amt']);

        $taxInvoices = TaxInvoice::where('invoice_date', $date)
            ->where('customer_id', $customerId)
            ->where('invoice_status','<>','Cancelled')
            ->get(['invoice_num','qty','net_amt']);

        $bulkInvoices = BulkMilkOrder::where('invoice_date', $date)
            ->where('customer_id', $customerId)
            ->where('invoice_status','<>','Cancelled')
            ->get(['invoice_num','net_amt']);
        foreach($bulkInvoices as &$invoice) {
            $invoice->qty = BulkMilkOrderItem::where('invoice_num', $invoice->invoice_num)->sum('qty_ltr');
        }    

        return $salesInvoices->concat($taxInvoices)->concat($bulkInvoices);
    }

    private function getReceiptAmountWithBank($customerId, $date) {
        $receipts = Receipt::select('receipt_num','bank_id','amount')
            ->with('bank:id,display_name')
            ->where('receipt_date', $date)
            ->where('customer_id', $customerId)
            ->where('status', 'Approved')
            ->where('mode', 'Bank')
            ->get();        
        return $receipts;
    }
/* Customer Report - Section End */


/* Transaction Report - Section Start */
    public function transactionReport(Request $request)
    {
        $from_date = $request->input('from_date', date('Y-m-d'));
        $to_date = $request->input('to_date', $from_date);
        $type = $request->input('type') ?? "All";

        // Fetch transaction data
        $result = $this->transactionData($from_date, $to_date, $type);

        // return response()->json([
        return view('reports.transaction-report', [
            'dates'    => ['from' => $from_date, 'to' => $to_date],
            'type'     => $type,
            'records'  => $result['data'],
            'totals'   => $result['totals']
        ]);
    }

    public function transactionData($from_date, $to_date, $type)
    {
        $banks = BankMaster::select('id','display_name')->orderBy('id')->get();
        $dates = getDatesForLoop($from_date, $to_date);
        $data = [];
        
        if($type == "All") {
            foreach ($dates as $date) {
                $this->generateInvoiceRecordsForTR($data, $date, SalesInvoice::class, 'Sales Invoice');
                $this->generateInvoiceRecordsForTR($data, $date, TaxInvoice::class, 'Tax Invoice');
                $this->generateInvoiceRecordsForTR($data, $date, BulkMilkOrder::class, 'Bulk Milk');
                $this->generateReceiptRecordsForTR($data, $date, 'Cash');
                foreach($banks as $bank)
                    $this->generateBankReceiptRecordsForTR($data, $date, $bank);
                $this->generateReceiptRecordsForTR($data, $date, 'Incentive');
                $this->generateReceiptRecordsForTR($data, $date, 'Deposit');
                $this->generateSalesReturnRecordsForTR($data, $date);
                $this->generateCreditNoteRecordsForTR($data, $date);
            }
        }
        else if($type == "Sales Invoice") {
            foreach ($dates as $date) {
                $this->generateInvoiceRecordsForTR($data, $date, SalesInvoice::class, 'Sales Invoice');
            }
        }
        else if($type == "Tax Invoice") {
            foreach ($dates as $date) {
                $this->generateInvoiceRecordsForTR($data, $date, TaxInvoice::class, 'Tax Invoice');
            }
        }
        else if($type == "Bulk Milk Invoice") {
            foreach ($dates as $date) {
                $this->generateInvoiceRecordsForTR($data, $date, BulkMilkOrder::class, 'Bulk Milk');
            }
        }
        else if($type == "Cash Receipt") {
            foreach ($dates as $date) {
                $this->generateReceiptRecordsForTR($data, $date, 'Cash');
            }
        }
        else if($type == "Bank Receipt") {
            foreach ($dates as $date) {
                foreach($banks as $bank) {
                    $this->generateBankReceiptRecordsForTR($data, $date, $bank);
                }
            }
        }
        else if($type == "Incentive Receipt") {
            foreach ($dates as $date) {
                $this->generateReceiptRecordsForTR($data, $date, 'Incentive');
            }
        }
        else if($type == "Deposit Receipt") {
            foreach ($dates as $date) {
                $this->generateReceiptRecordsForTR($data, $date, 'Deposit');
            }
        }
        else if($type == "Sales Return") {
            foreach ($dates as $date) {
                $this->generateSalesReturnRecordsForTR($data, $date);
            }
        }
        else if($type == "Credit Note") {
            foreach ($dates as $date) {
                $this->generateCreditNoteRecordsForTR($data, $date);
            }
        }

        $totals = [
            'debit'  => 0,
            'credit' => 0
        ];

        foreach ($data as $record) {
            $totals['debit']  += (float)($record['debit'] ?? 0);
            $totals['credit'] += (float)($record['credit'] ?? 0);
        }
        
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumber($value);
        }

        return [
            'data' => $data,
            'totals' => $totals
        ];
    }

    private function generateInvoiceRecordsForTR(&$data, $date, $model, $type) // TR - Transaction Report
    {        
        $invoices = $model::select('id','invoice_num','customer_id','customer_name','net_amt')
            ->with('customer:id,customer_code')
            ->where('invoice_date', $date->format('Y-m-d'))
            ->where('invoice_status','<>','Cancelled')
            ->get();

        foreach($invoices as $invoice) {
            $record['date']     = $date->format('d-m-Y');
            $record['type']     = $type;
            $record['number']   = $invoice->invoice_num;
            $record['code']     = $invoice->customer->customer_code;
            $record['customer'] = $invoice->customer_name;
            $record['debit']    = $invoice->net_amt;
            $record['credit']   = "";
            $data[] = $record;
        }
    }
    
    private function generateReceiptRecordsForTR(&$data, $date, $mode)
    {
        $receipts = Receipt::select('id','receipt_num','customer_id','customer_name','amount')
            ->with('customer:id,customer_code')
            ->where('receipt_date', $date->format('Y-m-d'))
            ->where('status','Approved')
            ->where('mode',$mode)
            ->get();

        foreach($receipts as $receipt) {
            $record['date']     = $date->format('d-m-Y');
            $record['type']     = $mode . " Receipt";
            $record['number']   = $receipt->receipt_num;
            $record['code']     = $receipt->customer->customer_code;
            $record['customer'] = $receipt->customer_name;
            $record['debit']    = "";
            $record['credit']   = $receipt->amount;
            $data[] = $record;
        }
    }

    private function generateBankReceiptRecordsForTR(&$data, $date, $bank)
    {
        $receipts = Receipt::select('id','receipt_num','customer_id','customer_name','amount')
            ->with('customer:id,customer_code')
            ->where('receipt_date', $date->format('Y-m-d'))
            ->where('status','Approved')
            ->where('mode','Bank')
            ->where('bank_id',$bank->id)
            ->get();

        foreach($receipts as $receipt) {
            $record['date']     = $date->format('d-m-Y');
            $record['type']     = "Bank Receipt [{$bank->display_name}]";
            $record['number']   = $receipt->receipt_num;
            $record['code']     = $receipt->customer->customer_code;
            $record['customer'] = $receipt->customer_name;
            $record['debit']    = "";
            $record['credit']   = $receipt->amount;
            $data[] = $record;
        }
    }

    private function generateSalesReturnRecordsForTR(&$data, $date)
    {
        $salesReturns = SalesReturn::select('id','txn_id','customer_id','net_amt')
            ->with('customer:id,customer_name,customer_code')
            ->where('txn_date', $date->format('Y-m-d'))
            ->get();

        foreach($salesReturns as $salesReturn) {
            $record['date']     = $date->format('d-m-Y');
            $record['type']     = "Sales Return";
            $record['number']   = $salesReturn->txn_id;
            $record['code']     = $salesReturn->customer->customer_code;
            $record['customer'] = $salesReturn->customer->customer_name;
            $record['debit']    = "";
            $record['credit']   = $salesReturn->net_amt;
            $data[] = $record;
        }
    }

    private function generateCreditNoteRecordsForTR(&$data, $date)
    {
        $creditNotes = CreditNote::select('id','document_number','customer_id','amount')
            ->with('customer:id,customer_name,customer_code')
            ->where('document_date', $date->format('Y-m-d'))
            ->where('status',DocumentStatus::APPROVED)
            ->get();

        foreach($creditNotes as $creditNote) {
            $record['date']     = $date->format('d-m-Y');
            $record['type']     = "Credit Note";
            $record['number']   = $creditNote->document_number;
            $record['code']     = $creditNote->customer->customer_code;
            $record['customer'] = $creditNote->customer->customer_name;
            $record['debit']    = "";
            $record['credit']   = $creditNote->amount;
            $data[] = $record;
        }
    }
/* Transaction Report - Section End */

/* Item wise Customer Report - Section Start */
    public function itemWiseCustomerReport(Request $request)
    {        
        $products = Product::select('id','short_name','tax_type','gst')
                    ->where('visible_invoice','1')                    
                    ->orderBy('display_index')
                    ->get(); 
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $productId = $request->input('productId') ?? 0;
        $productName = null;
        if($productId)
        {
            $productName = Product::find($productId)?->short_name ?? '';
            $data = $this->getItemWiseCustomerData($fromDate, $toDate, $productId);
        }else
        {
            $data = [];
        }       

        // return response()->json([
        return view('reports.itemwise-customer-report', [
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,            
            'records'    => $data,
            'products'   => $products,
            'productId'  => $productId,
            'productName'=> $productName
        ]);
    }

    public function getItemWiseCustomerData($fromDate, $toDate, $productId)
    {       
        $data = [] ;
        $totalQty = 0;
        $totalAmount = 0;

        // Fetch Sales Invoice Items
        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->pluck('invoice_num');        

        foreach ($salesInvNums as $sInvoice) {
            $salesInvoiceItems = SalesInvoiceItem::select('invoice_num', 'product_id', 'product_name', 'item_category', 'qty', 'amount')
                                ->where('invoice_num', $sInvoice)
                                ->where('product_id', $productId)
                                ->get();
            
            foreach ($salesInvoiceItems as $invoice) {
                $salesInv = SalesInvoice::where('invoice_num', $invoice->invoice_num)->first();           
                $data[] = [
                    'date'        => getIndiaDate($salesInv->invoice_date),
                    'invoice_num' => $salesInv->invoice_num,
                    'customer'    => $salesInv->customer_name,
                    'route'       => $salesInv->route_name,
                    'category'    => $invoice->item_category,
                    'qty'         => $invoice->qty,
                    'amount'      => $invoice->amount
                ];
                
                // Add to totals
                $totalQty += $invoice->qty;
                $totalAmount += $invoice->amount;
            }
        }

        // Fetch Tax Invoice Items
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])->pluck('invoice_num');

        foreach ($taxInvNums as $tInvoice) {
            $taxInvoiceItems = TaxInvoiceItem::select('invoice_num', 'product_id', 'product_name', 'item_category', 'qty', 'tot_amt')
                                ->where('invoice_num', $tInvoice)
                                ->where('product_id', $productId)
                                ->get();

            foreach ($taxInvoiceItems as $invoice) {
                $taxInv = TaxInvoice::where('invoice_num', $invoice->invoice_num)->first();           

                $data[] = [
                    'date'        => getIndiaDate($taxInv->invoice_date),
                    'invoice_num' => $taxInv->invoice_num,
                    'customer'    => $taxInv->customer_name,
                    'route'       => $taxInv->route_name,
                    'category'    => $invoice->item_category,
                    'qty'         => $invoice->qty,
                    'amount'      => $invoice->tot_amt
                ];

                // Add to totals
                $totalQty += $invoice->qty;
                $totalAmount += $invoice->tot_amt;
            }
        }

        // Fetch Bulk Milk Order Items
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])->pluck('invoice_num');

        foreach ($bulkInvNums as $bInvoice) {
            $bulkMilkItems = BulkMilkOrderItem::select('id', 'invoice_num', 'product_id', 'product_name', 'qty_ltr', 'amount')
                                ->where('invoice_num', $bInvoice)
                                ->where('product_id', $productId)
                                ->get();

            foreach ($bulkMilkItems as $invoice) {
                $bulkInv = BulkMilkOrder::where('invoice_num', $invoice->invoice_num)->first();         
                $customer = Customer::select('id', 'customer_name', 'route_id')
                            ->with('route:id,name')
                            ->where('id', $bulkInv->customer_id)
                            ->first(); 

                $data[] = [
                    'date'        => getIndiaDate($bulkInv->invoice_date),
                    'invoice_num' => $bulkInv->invoice_num,
                    'customer'    => $bulkInv->customer_name,
                    'route'       => $customer->route->name,
                    'category'    => "", 
                    'qty'         => $invoice->qty_ltr,
                    'amount'      => $invoice->amount
                ];

                // Add to totals
                $totalQty += $invoice->qty_ltr;
                $totalAmount += $invoice->amount;
            }
        }

        // Append totals at the end
        $data[] = [
            'date'        => 'Total',
            'invoice_num' => '',
            'customer'    => '',
            'route'       => '',
            'category'    => '',
            'qty'         => $totalQty,
            'amount'      => $totalAmount
        ];

        return $data;
    }
/* Item wise Customer Report - Section End */

/* Customer wise Item Report - Section Start */
    public function customerWiseitemReport(Request $request)
    {        
        // dd($request->all());
        $customerId = $request->input('customerId') ?? 0;
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $customer = Customer::find($customerId);

        $customers = Customer::select('id', 'customer_name')
            ->where('status', 'Active')
            ->orderBy('customer_name')
            ->get();        

        $data = $this->getCustomerWiseItemData($fromDate, $toDate, $customerId);

        // return response()->json([
        return view('reports.customerwise-item-report', [
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,            
            'records'    => $data['data'] ?? [],
            'totals'     => $data['totals'] ?? [],
            'summary'    => $data['product_summary'] ?? [],
            'customers'  => $customers,
            'customerId' => $customerId,
            'customer'   => $customer
        ]);
    }

    public function getCustomerWiseItemData($fromDate, $toDate, $customerId)
    {       
        $data = [] ;
        $totalQty = 0;
        $totalAmount = 0;
        $productSummary = [];

        // Fetch Sales Invoice Items
        $salesInvNums = SalesInvoice::whereBetween('invoice_date', [$fromDate, $toDate])
                        ->where('customer_id', $customerId)
                        ->pluck('invoice_num');        

        foreach ($salesInvNums as $sInvoice) {
            $salesInvoiceItems = SalesInvoiceItem::select('invoice_num', 'product_id', 'product_name', 'item_category', 'qty', 'amount')
                                ->where('invoice_num', $sInvoice)
                                ->get();
            
            foreach ($salesInvoiceItems as $invoice) {
                $salesInv = SalesInvoice::where('invoice_num', $invoice->invoice_num)->first();           
                $data[] = [
                    'date'        => $salesInv->invoice_date,  // Use raw date for sorting
                    'invoice_num' => $salesInv->invoice_num,
                    'product'     => $invoice->product_name,                   
                    'category'    => $invoice->item_category,
                    'qty'         => $invoice->qty,
                    'amount'      => $invoice->amount
                ];
                
                // Add to totals
                $totalQty += $invoice->qty;
                $totalAmount += $invoice->amount;

                // Track product summary
                if (!isset($productSummary[$invoice->product_name])) {
                    $productSummary[$invoice->product_name] = ['qty' => 0, 'amount' => 0];
                }
                $productSummary[$invoice->product_name]['qty'] += $invoice->qty;
                $productSummary[$invoice->product_name]['amount'] += $invoice->amount;
            }
        }

        // Fetch Tax Invoice Items
        $taxInvNums = TaxInvoice::whereBetween('invoice_date', [$fromDate, $toDate])
                        ->where('customer_id', $customerId)
                        ->pluck('invoice_num');

        foreach ($taxInvNums as $tInvoice) {
            $taxInvoiceItems = TaxInvoiceItem::select('invoice_num', 'product_id', 'product_name', 'item_category', 'qty', 'tot_amt')
                                ->where('invoice_num', $tInvoice)
                                ->get();

            foreach ($taxInvoiceItems as $invoice) {
                $taxInv = TaxInvoice::where('invoice_num', $invoice->invoice_num)->first();           

                $data[] = [
                    'date'        => $taxInv->invoice_date, // Use raw date for sorting
                    'invoice_num' => $taxInv->invoice_num,
                    'product'     => $invoice->product_name,
                    'category'    => $invoice->item_category,
                    'qty'         => $invoice->qty,
                    'amount'      => $invoice->tot_amt
                ];

                // Add to totals
                $totalQty += $invoice->qty;
                $totalAmount += $invoice->tot_amt;

                // Track product summary
                if (!isset($productSummary[$invoice->product_name])) {
                    $productSummary[$invoice->product_name] = ['qty' => 0, 'amount' => 0];
                }
                $productSummary[$invoice->product_name]['qty'] += $invoice->qty;
                $productSummary[$invoice->product_name]['amount'] += $invoice->tot_amt;
            }
        }

        // Fetch Bulk Milk Order Items
        $bulkInvNums = BulkMilkOrder::whereBetween('invoice_date', [$fromDate, $toDate])
                        ->where('customer_id', $customerId)
                        ->pluck('invoice_num');

        foreach ($bulkInvNums as $bInvoice) {
            $bulkMilkItems = BulkMilkOrderItem::select('id', 'invoice_num', 'product_id', 'product_name', 'qty_ltr', 'amount')
                                ->where('invoice_num', $bInvoice)
                                ->get();

            foreach ($bulkMilkItems as $invoice) {
                $bulkInv = BulkMilkOrder::where('invoice_num', $invoice->invoice_num)->first();         

                $data[] = [
                    'date'        => $bulkInv->invoice_date, // Use raw date for sorting
                    'invoice_num' => $bulkInv->invoice_num,
                    'product'     => $invoice->product_name,
                    'category'    => "", 
                    'qty'         => $invoice->qty_ltr,
                    'amount'      => $invoice->amount
                ];

                // Add to totals
                $totalQty += $invoice->qty_ltr;
                $totalAmount += $invoice->amount;

                // Track product summary
                if (!isset($productSummary[$invoice->product_name])) {
                    $productSummary[$invoice->product_name] = ['qty' => 0, 'amount' => 0];
                }
                $productSummary[$invoice->product_name]['qty'] += $invoice->qty_ltr;
                $productSummary[$invoice->product_name]['amount'] += $invoice->amount;
            }
        }       

        // Sort data by date (ascending order)
        usort($data, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Format the date after sorting
        foreach ($data as &$entry) {
            $entry['date'] = getIndiaDate($entry['date']);
        }

        return [
            'data' => $data,  // Contains all invoice data sorted by date
            'totals' => [
                'qty'    => $totalQty,
                'amount' => $totalAmount
            ],
            'product_summary' => $productSummary // Product-wise summary
        ];
    }
/* Customer wise Item Report - Section End */

/* Zero Value Items Report - Section Start */
    public function zeroValueItemsReport(Request $request)
    {
        $from_date     = $request->input('from_date', date('Y-m-d'));
        $to_date       = $request->input('to_date', $from_date);
        $report_type   = $request->input('report_type', 'Itemized');
        $invoice_type  = $request->input('invoice_type', 'Both');
        $customer_id   = $request->input('customer_id', 0);
        $customer_name = $request->input('customer_name', "");
        $item_id       = $request->input('item_id', 0);
        $item_name     = $request->input('item_name', "");
        
        $this->dates = [$from_date, $to_date];
        if($report_type === "Itemized")
            $records = $this->getZeroValueItemizedData($invoice_type, $customer_id, $item_id); 
        else
            $records = $this->getZeroValueSummaryData($customer_id);

        // Compute overall totals
        $totals = $records?->count() ? [
            'sample'   => $records->sum(fn($row) => (float) ($row['sample'] ?? 0)),
            'damage'   => $records->sum(fn($row) => (float) ($row['damage'] ?? 0)),
            'spoilage' => $records->sum(fn($row) => (float) ($row['spoilage'] ?? 0)),
            'free'     => $records->sum(fn($row) => (float) ($row['free'] ?? 0)),
            'total'    => $records->sum(fn($row) => (float) ($row['total'] ?? 0)),
        ] : null;

        // return response()->json([
        return view('reports.sales.zero-value-items-report', [
            'dates'        => ['from' => $from_date, 'to' => $to_date],
            'customer'     => ['id' => $customer_id, 'name' => $customer_name],
            'item'         => ['id' => $item_id, 'name' => $item_name],
            'report_type'  => $report_type,
            'invoice_type' => $invoice_type,
            'records'      => $records,            
            'totals'       => $totals,
        ]);
    }

    private function getZeroValueItemizedData($invoice_type, $customer_id, $item_id)
    {
        $isSales = $invoice_type != "Tax";
        $isTax   = $invoice_type != "Sales";

        $exemptedRecords = collect($isSales ? $this->getZeroValueExemptedData($customer_id, $item_id) : []);
        $taxRecords      = collect($isTax   ? $this->getZeroValueTaxData($customer_id, $item_id)     : []);

        // Merge both collections and then sort
        $records = $exemptedRecords
            ->merge($taxRecords) // Merge collections based on selected filters
            ->sortBy('date') // Sort by 'date' ascending
            ->values() // Re-index the array (optional but cleaner)
            ->map(function ($item, $index) { // Add serial number
                return array_merge(['sno' => $index + 1], $item);
            });

        return $records;
    }

    private function getZeroValueExemptedData($customer_id, $item_id)
    {
        $query = SalesInvoiceItem::select(
                'sales_invoices.invoice_date',
                'sales_invoices.invoice_num',
                'sales_invoices.customer_name',
                'sales_invoice_items.product_name',
                'sales_invoice_items.item_category',
                'sales_invoice_items.qty'
            )
            ->join('sales_invoices', 'sales_invoice_items.invoice_num', '=', 'sales_invoices.invoice_num')
            ->whereBetween('sales_invoices.invoice_date', $this->dates)
            ->where('sales_invoices.invoice_status', 'Generated')
            ->where('sales_invoice_items.item_category', '<>', 'Regular');

        if ($customer_id != 0)
            $query->where('sales_invoices.customer_id', $customer_id);
        if ($item_id != 0)
            $query->where('sales_invoice_items.product_id', $item_id);

        $records = $query->get();

        return $this->getZeroValueItemizedRecords($records);
    }

    private function getZeroValueTaxData($customer_id, $item_id)
    {
        $query = TaxInvoiceItem::select(
                'tax_invoices.invoice_date',
                'tax_invoices.invoice_num',
                'tax_invoices.customer_name',
                'tax_invoice_items.product_name',
                'tax_invoice_items.item_category',
                'tax_invoice_items.qty'
            )
            ->join('tax_invoices', 'tax_invoice_items.invoice_num', '=', 'tax_invoices.invoice_num')
            ->whereBetween('tax_invoices.invoice_date', $this->dates)
            ->where('tax_invoices.invoice_status', 'Generated')
            ->where('tax_invoice_items.item_category', '<>', 'Regular');

        if ($customer_id != 0)
            $query->where('tax_invoices.customer_id', $customer_id);
        if ($item_id != 0)
            $query->where('tax_invoice_items.product_id', $item_id);

        $records = $query->get();

        return $this->getZeroValueItemizedRecords($records);
    }

    private function getZeroValueItemizedRecords($records)
    {
        // Transform the records: group and pivot
        $grouped = $records->groupBy(function ($item) {
            return $item->invoice_date . '|' . $item->invoice_num . '|' . $item->customer_name . '|' . $item->product_name;
        })->map(function ($items) {
            $first = $items->first();

            $sample   = optional($items->firstWhere('item_category', 'Sample'))->qty ?? 0;
            $damage   = optional($items->firstWhere('item_category', 'Damage'))->qty ?? 0;
            $spoilage = optional($items->firstWhere('item_category', 'Spoilage'))->qty ?? 0;
            $free     = optional($items->firstWhere('item_category', 'Free'))->qty ?? 0;
            $total    = $sample + $damage + $spoilage + $free;  

            return [
                'date'     => displayDate($first->invoice_date),
                'number'   => $first->invoice_num,
                'customer' => $first->customer_name,
                'item'     => $first->product_name,
                'sample'   => $sample   ?: '',
                'damage'   => $damage   ?: '',
                'spoilage' => $spoilage ?: '',
                'free'     => $free ?: '',
                'total'    => $total    ?: '',
            ];
        })->values(); // Reset numeric keys

        return $grouped;
    }

    private function getZeroValueSummaryData($customer_id)
    {

    }
/* Zero Value Items Report - Section End */

/* Bill Wise Report - Section Start */
    public function billWiseReport(Request $request)
    {
        $from_date     = $request->input('from_date', date('Y-m-d'));
        $to_date       = $request->input('to_date', $from_date);
        $customer_id   = $request->input('customer_id', 0);
        $customer_name = $request->input('customer_name', "");
        $status_type   = $request->input('status_type', "All");

        // Fetch billwise data
        $data = $this->billWiseData([$from_date, $to_date], $customer_id, $status_type);

        // return response()->json([
        return view('reports.billwise-report', [
            'dates'       => ['from' => $from_date, 'to' => $to_date],
            'customer'    => ['id' => $customer_id, 'name' => $customer_name],
            'status_type' => $status_type,
            'records'     => $data['records'],
            'totals'      => $data['totals']
        ]);
    }

    public function billWiseData($dates, $customer_id, $status_type)
    {
        // Get invoice records
        $invoiceRecords = $this->getBillWiseInvoiceData($dates, $customer_id);

        // Fetch receipts by invoice_num         
        $receiptRecords = ReceiptData::select('receipt_id','receipt_date','invoice_number','amount','receipt_status')
            ->with('receipt:id,receipt_num')
            ->whereIn('invoice_number',$invoiceRecords->pluck('invoice_num'))
            ->get();

        // Group receipts by invoice_number
        $receiptsGrouped = $receiptRecords->groupBy('invoice_number');

        if ($status_type === 'Zero Value') {
            $records = $invoiceRecords
                ->filter(function ($invoice) {
                    return $invoice['net_amt'] == 0;
                })
                ->map(function ($invoice) {
                    $invoice['receipts'] = [];
                    $invoice['discount'] = 0;
                    $invoice['outstanding'] = 0;
                    $invoice['status'] = 'Zero Value';
                    return $invoice;
                })
                ->values(); // reindex the collection
        }
        else {
            // Attach matching receipts to each invoice and calculate outstanding and status
            $records = $invoiceRecords->map(function ($invoice) use ($receiptsGrouped) {
                if($invoice['net_amt'] != 0) {
                    $receipts = $receiptsGrouped->get($invoice['invoice_num'], collect());
                    $receivedAmount = $receipts->sum('amount');
                    $outstanding = $invoice['net_amt'] - $receivedAmount;

                    $invoice['receipts'] = $receipts->values();
                    $invoice['outstanding'] = $outstanding;                    
                    $invoice['status'] = $outstanding <= 0 ? 'Paid' : 'Outstanding';                    
                }
                else {
                    $invoice['receipts'] = [];
                    $invoice['discount'] = 0;
                    $invoice['outstanding'] = 0;                    
                    $invoice['status'] = 'Zero Value';
                }
                return $invoice;
            });

            // Filter based on status_type
            if ($status_type === 'Paid')
                $records = $records->where('status', 'Paid')->values();
            elseif ($status_type === 'Outstanding')
                $records = $records->where('status', 'Outstanding')->values();
        }

        return [
            'records' => $records,
            'totals'  => $this->calculateBillWiseTotals($records),
        ];
    }

    private function getBillWiseInvoiceData($dates, $customer_id)
    {
        $baseQuery = function ($model) use ($dates, $customer_id) {
            return $model::select('invoice_date', 'invoice_num', 'customer_name', 'net_amt')
                ->whereBetween('invoice_date', $dates)
                ->where('invoice_status', '<>', 'Cancelled')
                ->when($customer_id != 0, fn($q) => $q->where('customer_id', $customer_id))
                ->get();
        };

        $salesRecords = $baseQuery(SalesInvoice::class);
        $taxRecords = $baseQuery(TaxInvoice::class);
        $bulkMilkRecords = $baseQuery(BulkMilkOrder::class);

        // Merge and sort by invoice_date
        $records = collect()
            ->merge($salesRecords)
            ->merge($taxRecords)
            ->merge($bulkMilkRecords)
            ->sortBy('invoice_date')
            ->values(); // reindex after sorting

        return $records;
    }

    private function calculateBillWiseTotals($records)
    {
        $inv_amt   = $records->sum('net_amt');
        $oustd_amt = $records->sum('outstanding');
        $rcpt_amt  = $inv_amt - $oustd_amt;

        return [
            'inv_amt'   => formatIndianNumber($inv_amt),
            'rcpt_amt'  => formatIndianNumber($rcpt_amt),
            'oustd_amt' => formatIndianNumber($oustd_amt),
        ];
    }
/* Bill Wise Report - Section End */

/* Document Report - Section Start */
    public function documentReport(Request $request)
    {
        $from = $request->input('from_date', date('Y-m-d'));
        $to   = $request->input('to_date', $from);
        $this->dates = [$from, $to];        

        $records = [
            $this->getDocumentData("Sales Invoices",     SalesInvoice::class,  'invoice_date',  'invoice_num'),
            $this->getDocumentData("Tax Invoices",       TaxInvoice::class,    'invoice_date',  'invoice_num'),
            $this->getDocumentData("Bulk Milk Invoices", BulkMilkOrder::class, 'invoice_date',  'invoice_num'),
            $this->getDocumentData("Job Works",          JobWork::class,       'job_work_date', 'job_work_num'),
            $this->getDocumentData("Orders",             Order::class,         'invoice_date',  'order_num'),
            $this->getDocumentData("Receipts",           Receipt::class,       'receipt_date',  'receipt_num',  'status', 'Pending', 'Cancelled'),
            $this->getDocumentDataOfSalesReturn(),
            $this->getDocumentDataOfCreditNotes(),
        ];

        // return response()->json([
        return view('reports.document-report', [
            'dates'   => ['from' => $from, 'to' => $to],
            'records' => $records,
        ]);
    }

    public function documentDetail(Request $request)
    {
        $document_type = $request->document_type;
        $count_type = $request->count_type;
        $from = $request->from_date;
        $to = $request->to_date;
        $this->dates = [$from, $to];

        $table = $this->getDocumentRecords($document_type, $count_type);

        return response()->json($table);
    }

    private function getDocumentData($document, $model, $dateField, $numberField, $statusField = 'invoice_status', $pendingValue = 'Not Generated', $cancelledValue = 'Cancelled')
    {
        $data = $model::whereBetween($dateField, $this->dates)->select($numberField, $statusField)->get();
        
        if ($data->isEmpty()) {
            return [
                'document'  => $document,
                'from'      => null,
                'to'        => null,
                'total'     => 0,
                'pending'   => 0,
                'cancelled' => 0,
                'net_total' => 0,
            ];
        }

        $total     = $data->count();
        $pending   = $data->where($statusField, $pendingValue)->count();
        $cancelled = $data->where($statusField, $cancelledValue)->count();

        return [
            'document'  => $document,
            'from'      => $data->first()->$numberField,
            'to'        => $data->last()->$numberField,
            'total'     => $total,
            'pending'   => $pending,
            'cancelled' => $cancelled,
            'net_total' => $total - $pending - $cancelled,
        ];    
    }

    private function getDocumentDataOfSalesReturn()
    {
        $returns = SalesReturn::whereBetween('txn_date', $this->dates)
            ->get(['txn_id']);

        if ($returns->isEmpty()) {
            return [
                'document'  => 'Sales Returns',
                'from'      => null,
                'to'        => null,
                'total'     => 0,
                'pending'   => 0,
                'cancelled' => 0,
                'net_total' => 0,
            ];
        }

        $count = $returns->count();

        return [
            'document'  => 'Sales Returns',
            'from'      => $returns->first()->txn_id,
            'to'        => $returns->last()->txn_id,
            'total'     => $count,
            'pending'   => 0,
            'cancelled' => 0,
            'net_total' => $count,
        ];
    }

    private function getDocumentDataOfCreditNotes()
    {
        $data = CreditNote::whereBetween('document_date', $this->dates)
            ->get(['document_number', 'status']);

        if ($data->isEmpty()) {
            return [
                'document'  => 'Credit Notes',
                'from'      => null,
                'to'        => null,
                'total'     => 0,
                'pending'   => 0,
                'cancelled' => 0,
                'net_total' => 0,
            ];
        }

        $total = $data->count();
        $pending = $data->where('status', DocumentStatus::DRAFT)->count();
        $cancelled = $data->where('status', DocumentStatus::CANCELLED)->count();

        return [
            'document'  => 'Credit Notes',
            'from'      => $data->first()->document_number,
            'to'        => $data->last()->document_number,
            'total'     => $total,
            'pending'   => $pending,
            'cancelled' => $cancelled,
            'net_total' => $total - $pending - $cancelled,
        ];
    }

    private function getDocumentRecords($document_type, $count_type)
    {
        $titles = ["Date", "Number", "Route", "Customer", "Amount"];
        $alignments = ["text-center", "text-center", "text-left pl-2", "text-left pl-2", "text-right pr-2"];
        $records = [];

        $statusMap = [
            'cancelled' => 'Cancelled',
            'net_total' => 'Generated',
            'pending'   => 'Not Generated',
        ];

        $status = $statusMap[$count_type] ?? null;

        switch ($document_type) {
            case "Sales Invoices":
                $query = SalesInvoice::select('invoice_date', 'invoice_num', 'route_name', 'customer_name', 'net_amt')
                    ->whereBetween('invoice_date', $this->dates);
                if ($status) $query->where('invoice_status', $status);
                $invoices = $query->get();
                foreach ($invoices as $invoice) {
                    $records[] = [
                        displayDate($invoice->invoice_date),
                        $invoice->invoice_num,
                        $invoice->route_name,
                        $invoice->customer_name,
                        $invoice->net_amt,
                    ];
                }
                break;

            case "Tax Invoices":
                $query = TaxInvoice::select('invoice_date', 'invoice_num', 'route_name', 'customer_name', 'net_amt')
                    ->whereBetween('invoice_date', $this->dates);
                if ($status) $query->where('invoice_status', $status);
                $invoices = $query->get();
                foreach ($invoices as $invoice) {
                    $records[] = [
                        displayDate($invoice->invoice_date),
                        $invoice->invoice_num,
                        $invoice->route_name,
                        $invoice->customer_name,
                        $invoice->net_amt,
                    ];
                }
                break;

            case "Bulk Milk Invoices":
                $query = BulkMilkOrder::select('invoice_date', 'invoice_num', 'route_name', 'customer_name', 'net_amt')
                    ->whereBetween('invoice_date', $this->dates);
                if ($status) $query->where('invoice_status', $status);
                $invoices = $query->get();
                foreach ($invoices as $invoice) {
                    $records[] = [
                        displayDate($invoice->invoice_date),
                        $invoice->invoice_num,
                        $invoice->route_name,
                        $invoice->customer_name,
                        $invoice->net_amt,
                    ];
                }
                break;

            case "Job Works":
                $titles = ["Date", "Number", "Route", "Customer"];
                $alignments = ["text-center", "text-center", "text-left pl-2", "text-left pl-2"];
                $query = JobWork::select('job_work_date', 'job_work_num', 'route_name', 'customer_name')
                    ->whereBetween('job_work_date', $this->dates);
                if ($status) $query->where('invoice_status', $status);
                $jobs = $query->get();
                foreach ($jobs as $job) {
                    $records[] = [
                        displayDate($job->job_work_date),
                        $job->job_work_num,
                        $job->route_name,
                        $job->customer_name,
                    ];
                }
                break;

            case "Orders":
                $titles = ["Date", "Number", "Route", "Customer"];
                $alignments = ["text-center", "text-center", "text-left pl-2", "text-left pl-2"];
                $query = Order::select('invoice_date', 'order_num', 'route_id', 'customer_id')
                    ->with(['route:id,name', 'customer:id,customer_name'])
                    ->whereBetween('invoice_date', $this->dates);
                if ($status) $query->where('invoice_status', $status);
                $orders = $query->get();
                foreach ($orders as $order) {
                    $records[] = [
                        displayDate($order->invoice_date),
                        $order->order_num,
                        $order->route->name ?? '',
                        $order->customer->customer_name ?? '',
                    ];
                }
                break;

            case "Receipts":
                $titles = ["Date", "Number", "Route", "Customer", "Mode", "Amount"];
                $alignments = ["text-center", "text-center", "text-left pl-2", "text-left pl-2", "text-center", "text-right pr-2"];
                $query = Receipt::select('receipt_date', 'receipt_num', 'route_id', 'customer_name', 'mode', 'amount')
                    ->with('route:id,name')
                    ->whereBetween('receipt_date', $this->dates);
                if ($count_type === 'pending') {
                    $query->where('status', 'Pending');
                } elseif ($count_type === 'net_total') {
                    $query->where('status', 'Approved');
                }
                $receipts = $query->get();
                foreach ($receipts as $receipt) {
                    $records[] = [
                        displayDate($receipt->receipt_date),
                        $receipt->receipt_num,
                        $receipt->route->name ?? '',
                        $receipt->customer_name,
                        $receipt->mode,
                        $receipt->amount,
                    ];
                }
                break;

            case "Sales Returns":
                $query = SalesReturn::select('txn_date', 'txn_id', 'route_id', 'customer_id', 'net_amt')
                    ->with(['route:id,name', 'customer:id,customer_name'])
                    ->whereBetween('txn_date', $this->dates);
                if (in_array($count_type, ['total', 'net_total'])) {
                    $returns = $query->get();
                    foreach ($returns as $return) {
                        $records[] = [
                            displayDate($return->txn_date),
                            $return->txn_id,
                            $return->route->name ?? '',
                            $return->customer->customer_name ?? '',
                            $return->net_amt,
                        ];
                    }
                }
                break;

            case "Credit Notes":
                $query = CreditNote::select('document_date', 'document_number', 'customer_id', 'amount')
                    ->with('customer:id,customer_name')
                    ->whereBetween('document_date', $this->dates);
                if (in_array($count_type, ['total', 'net_total'])) {
                    $notes = $query->get();
                    foreach ($notes as $note) {
                        $records[] = [
                            displayDate($note->document_date),
                            $note->document_number,
                            '',
                            $note->customer->customer_name ?? '',
                            $note->amount,
                        ];
                    }
                }
                break;
        }

        return [
            'titles' => $titles,
            'records' => $records,
            'alignments' => $alignments,
        ];
    }
/* Document Report - Section End */

/* Business Wise Report - Section Start */
    public function businessWiseReport(Request $request)
    {        
        $from_date     = $request->input('from_date', date('Y-m-d'));
        $to_date       = $request->input('to_date', $from_date);
        $report_type   = $request->input('report_type', 'Itemized');
        $business_type = $request->input('business_type', 'B2B');
        $this->dates   = [$from_date, $to_date];

        $report_title = $this->getBusinessWiseReportTitle($report_type, $business_type);

        if($business_type == "B2B")
            $customer_ids = Customer::whereIn('gst_type', ['Interstate Registered', 'Intrastate Registered'])->pluck('id');
        else if($business_type == "B2C")
            $customer_ids = Customer::whereIn('gst_type', ['Interstate Unregistered', 'Intrastate Unregistered'])->pluck('id');
        else
            $customer_ids = collect();

        $invoice_numbers = [
            'sales'     => $this->getInvoiceNumbers(SalesInvoice::class, $customer_ids),
            'tax'       => $this->getInvoiceNumbers(TaxInvoice::class, $customer_ids),
            'bulk-milk' => $this->getInvoiceNumbers(BulkMilkOrder::class, $customer_ids),
        ];

        if($report_type === "Itemized" ) {
            $filters = [
                'hsn_code' => $request->input('hsn_code', ''),
                'category' => $request->input('category', ''),
                'unit'     => $request->input('unit', ''),
                'tax_rate' => $request->input('tax_rate', ''),
            ];
            $data = $this->getItemizedBusinessWiseData($invoice_numbers, $filters);
            $filter_data = $this->getBusinessWiseReportFilters();
        }
        else {
            $data = $this->getHsnBasedBusinessWiseData($invoice_numbers);
            $filters = [];
            $filter_data = null;
        }

        // return response()->json([
        return view('reports.sales.business-wise-report', [
            'dates'         => ['from' => $from_date, 'to' => $to_date],
            'report_type'   => $report_type,
            'business_type' => $business_type,
            'filters'       => $filters,
            'report_title'  => $report_title,
            'records'       => $data['records'],
            'totals'        => $data['totals'],
            'filter_data'   => $filter_data,
        ]);
    }

    public function businessWiseExportSupport(Request $request) {
        $from_date     = $request->input('from_date', date('Y-m-d'));
        $to_date       = $request->input('to_date', $from_date);
        $report_type   = $request->input('report_type', 'Itemized');
        $business_type = $request->input('business_type', 'B2B');
        $this->dates   = [$from_date, $to_date];

        if($business_type == "B2B")
            $customer_ids = Customer::whereIn('gst_type', ['Interstate Registered', 'Intrastate Registered'])->pluck('id');
        else if($business_type == "B2C")
            $customer_ids = Customer::whereIn('gst_type', ['Interstate Unregistered', 'Intrastate Unregistered'])->pluck('id');
        else
            $customer_ids = collect();

        $invoice_numbers = [
            'sales'     => $this->getInvoiceNumbers(SalesInvoice::class, $customer_ids),
            'tax'       => $this->getInvoiceNumbers(TaxInvoice::class, $customer_ids),
            'bulk-milk' => $this->getInvoiceNumbers(BulkMilkOrder::class, $customer_ids),
        ];

        if($report_type === "Itemized" ) {
            $filters = [
                'hsn_code' => $request->input('hsn_code', ''),
                'category' => $request->input('category', ''),
                'unit'     => $request->input('unit', ''),
                'tax_rate' => $request->input('tax_rate', ''),
            ];
            $data = $this->getItemizedBusinessWiseData($invoice_numbers, $filters);            
        }
        else {
            $data = $this->getHsnBasedBusinessWiseData($invoice_numbers);
            $filters = [];            
        }

        return [        
            'dates'         => ['from' => $from_date, 'to' => $to_date],
            'report_type'   => $report_type,
            'business_type' => $business_type,
            'filters'       => $filters,            
            'records'       => $data['records'],
            'totals'        => $data['totals'],            
        ];
    }
    
    private function getBusinessWiseReportTitle($report_type, $business_type) {
        if ($business_type === 'Both') {
            $report_title = "Business Wise (" . $report_type . ") Report";
        }
        else {
            $report_title = $business_type . " ";
            $report_title .= $report_type . " Report";
        }
        return $report_title;
    }

    private function getInvoiceNumbers($model, $customerIds) {
        return $model::whereBetween('invoice_date', $this->dates)
            ->when($customerIds->isNotEmpty(), fn($query) => $query->whereIn('customer_id', $customerIds))
            ->where('invoice_status','<>','Cancelled')
            ->pluck('invoice_num');
    }

    private function getItemizedBusinessWiseData($invoice_numbers, $filters) {
        $records = [];
        $productList = Product::select('id','name','hsn_code','tax_type','gst')->orderBy('display_index')->get();        
        $this->generateHsnWiseRecords($records, $productList, $invoice_numbers['sales'], $invoice_numbers['tax']);
        $this->generateHsnWiseBulkMilkRecords($records, $productList, $invoice_numbers['bulk-milk']);

        $totals = [];
        if ($records) {
            // Apply filters only if at least one filter value is non-empty
            if (!empty(array_filter($filters))) {
                $records = collect($records)->filter(function ($record) use ($filters) {
                    return (empty($filters['hsn_code']) || $filters['hsn_code'] === $record['hsn_code']) &&
                           (empty($filters['category']) || $filters['category'] === $record['group']) &&
                           (empty($filters['unit'])     || $filters['unit']     === $record['uqc']) &&
                           (empty($filters['tax_rate']) || $filters['tax_rate'] === $record['tax_rate']);
                })->values()->toArray();
            }

            $this->addSNoColumn($records);
            $totals = $this->calculateHsnWiseTotalsFormat1($records);
        }

        return [
            'records' => $records,
            'totals' => $totals
        ];        
    }

    private function getHsnBasedBusinessWiseData($invoice_numbers) {
        $hsnList = GstMaster::select('hsn_code','description')->get();
        $records = [];
        foreach ($hsnList as $hsn) {
            $this->generateHsnBasedSalesRecords($records, $hsn, $invoice_numbers['sales'], $invoice_numbers['bulk-milk']);
            $this->generateHsnBasedTaxRecords($records, $hsn, $invoice_numbers['tax']);
        }

        $totals = [];
        if($records) {
            $this->addSNoColumn($records);
            $totals = $this->calculateHsnBasedTotals($records);
        }

        return [
            'records' => $records,
            'totals' => $totals
        ];
    }

    private function generateHsnBasedSalesRecords(&$records, $hsn, $salesInvNums, $bulkInvNums) {
        $amount = SalesInvoiceItem::whereIn('invoice_num', $salesInvNums)->where('hsn_code', $hsn->hsn_code)->sum('amount');
        $amount += BulkMilkOrderItem::whereIn('invoice_num', $bulkInvNums)->where('hsn_code', $hsn->hsn_code)->sum('amount');
        $qty = SalesInvoiceItem::whereIn('invoice_num', $salesInvNums)->where('hsn_code', $hsn->hsn_code)->sum('qty');
        $qty += BulkMilkOrderItem::whereIn('invoice_num', $bulkInvNums)->where('hsn_code', $hsn->hsn_code)->sum('qty_ltr');
        if ($amount) {
            $amt = getTwoDigitPrecision($amount);
            $records[] = [
                'hsn_name'    => $hsn->description,
                'hsn_code'    => $hsn->hsn_code,
                'qty'         => getTwoDigitPrecision($qty),
                'tax_rate'    => "0 %",                
                'taxable_amt' => $amt,
                'sgst'        => "",                   
                'cgst'        => "",                  
                'igst'        => "",                   
                'tax_amt'     => "",                    
                'gross_amt'   => $amt,
                'discount'    => "",
                'net_amt'     => $amt,
            ];            
        }
    }

    private function generateHsnBasedTaxRecords(&$records, $hsn, $taxInvNums) {
        $gstPerc = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                        ->where('hsn_code', $hsn->hsn_code)
                        ->distinct('gst')
                        ->get(['gst']);

        foreach($gstPerc as $item) {
            $summary = TaxInvoiceItem::whereIn('invoice_num', $taxInvNums)
                        ->where('hsn_code', $hsn->hsn_code)
                        ->where('gst', $item->gst)                        
                        ->selectRaw("SUM(qty) as qty, 
                                     SUM(amount) as amount,
                                     SUM(tax_amt) as tax_amt, 
                                     SUM(tot_amt) as tot_amt, 
                                     SUM(amount * sgst / 100) as sgst,
                                     SUM(amount * cgst / 100) as cgst,
                                     SUM(amount * igst / 100) as igst")
                        ->first();

            if ($summary->amount) {
                $records[] = [
                    'hsn_name'    => $hsn->description,
                    'hsn_code'    => $hsn->hsn_code,
                    'qty'         => getTwoDigitPrecision($summary->qty),
                    'tax_rate'    => $item->gst . " %",
                    'taxable_amt' => getTwoDigitPrecision($summary->amount),
                    'sgst'        => getTwoDigitPrecision($summary->sgst, ''),
                    'cgst'        => getTwoDigitPrecision($summary->cgst, ''),
                    'igst'        => getTwoDigitPrecision($summary->igst, ''),
                    'tax_amt'     => getTwoDigitPrecision($summary->tax_amt),
                    'gross_amt'   => getTwoDigitPrecision($summary->amount + $summary->tax_amt),
                    'discount'    => "",
                    'net_amt'     => getTwoDigitPrecision($summary->tot_amt),
                ];                
            }
        }
    }

    private function calculateHsnBasedTotals($records)
    {
        $totals = [
            'qty'         => 0,
            'taxable_amt' => 0,
            'sgst'        => 0,
            'cgst'        => 0,
            'igst'        => 0,
            'tax_amt'     => 0,
            'net_amt'     => 0,
        ];

        foreach ($records as $record) {
            $totals['qty']         += (float)($record['qty'] ?? 0);
            $totals['taxable_amt'] += (float)($record['taxable_amt'] ?? 0);
            $totals['sgst']        += (float)($record['sgst'] ?? 0);
            $totals['cgst']        += (float)($record['cgst'] ?? 0);
            $totals['igst']        += (float)($record['igst'] ?? 0);
            $totals['tax_amt']     += (float)($record['tax_amt'] ?? 0);
            $totals['net_amt']     += (float)($record['net_amt'] ?? 0);
        }
    
        // Apply precision to totals where applicable
        foreach ($totals as $key => $value) {
            $totals[$key] = formatIndianNumberWithDecimal($value);
        }

        return $totals;
    }

    private function getBusinessWiseReportFilters()
    {
        $hsn_codes = GstMaster::orderBy('hsn_code')->pluck('hsn_code');
        $categories = ProductGroup::orderBy('id')->pluck('name');
        $units = ['Liter', 'Kilogram', 'Numbers'];

        $tax_rates = GstMaster::pluck('gst')
            ->map(fn($rate) => ($rate ?? 0) . ' %') // Replace null with 0 and append " %"
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return [
            'hsn_codes'  => $hsn_codes,
            'categories' => $categories,
            'units'      => $units,
            'tax_rates'  => $tax_rates,
        ];
    }

/* Business Logic */    
    private function getOpeningBalance($customerId, $asOfDate)
    {
        // Get the date one day before the provided date
        $previousDate = Carbon::parse($asOfDate)->subDay()->endOfDay();
        
        $outstandingAmount = Outstanding::where('customer_id', $customerId)
        //  ->where('txn_date','<=',$date)
            ->where('status', 'Active')
            ->value('amount');

        // Fetch all amounts up to and including the previous day
        $salesInvoiceAmount = SalesInvoice::where('customer_id', $customerId)            
            ->where('invoice_date', '<=', $previousDate)
            ->where('invoice_status', 'Generated')
            ->sum('net_amt');

        $taxInvoiceAmount = TaxInvoice::where('customer_id', $customerId)            
            ->where('invoice_date', '<=', $previousDate)
            ->where('invoice_status', 'Generated')
            ->sum('net_amt');

        $bulkMilkInvoiceAmount = BulkMilkOrder::where('customer_id', $customerId)            
            ->where('invoice_date', '<=', $previousDate)
            ->where('invoice_status', 'Generated')
            ->sum('net_amt');

        $receiptAmount = Receipt::where('customer_id', $customerId)            
            ->where('receipt_date', '<=', $previousDate)
            ->where('status', 'Approved')
            ->sum('amount');

        $salesReturnAmount = SalesReturn::where('customer_id', $customerId)
            ->where('txn_date', '<=', $previousDate)            
            ->sum('net_amt');

        $openingBalance = ($outstandingAmount ?? 0)
                        + ($salesInvoiceAmount + $taxInvoiceAmount + $bulkMilkInvoiceAmount)
                        - $receiptAmount
                        - $salesReturnAmount;

        return $openingBalance;
    }
/* ---------------------------------- */

    public function testFunction(Request $request) {
        $dates = ['2025-04-01', '2025-04-31'];
        $customer = Customer::findOrFail(32);
        $record = [];

        $record['customer']  = $customer->customer_name;
        $record['open_bal']  = $this->getOpeningBalance($customer->id, $dates[0]);
        $record['inv_amt']   = $this->getInvoiceAmount($customer->id, $dates);
        $record['prev_inv']  = $this->getPreviousInvoiceAmount($customer->id, $dates[0]);
        $record['cash']      = $this->getReceiptAmount($customer->id, $dates, 'Cash');
        $record['bank']      = $this->getReceiptAmount($customer->id, $dates, 'Bank');
        $record['incentive'] = $this->getReceiptAmount($customer->id, $dates, 'Incentive');
        $record['deposit']   = $this->getReceiptAmount($customer->id, $dates, 'Deposit');
        $record['others']    = $this->getReturnAmount($customer->id, $dates);
        $record['day_bal']   = $record['inv_amt'] - $record['cash'] - $record['bank'] - $record['incentive'] - $record['deposit'] - $record['others'];        
        $record['close_bal'] = $record['open_bal'] + $record['inv_amt'] - $record['cash'] - $record['bank'] - $record['incentive'] - $record['deposit'] - $record['others'];

        if($record['prev_inv'])
            $record['yest_bal'] = $record['prev_inv'] - $record['cash'] - $record['bank'] - $record['incentive'] - $record['deposit'] - $record['others'];
        else
            $record['yest_bal'] = 0;
        
        return $record;
    }

    private function getPreviousInvoiceAmount($customerId, $date) {
        $date = getPreviousDate($date);

        $salInvAmt  = SalesInvoice::where('customer_id', $customerId)->where('invoice_date', $date)->where('invoice_status','<>','Cancelled')->sum('net_amt');
        $taxInvAmt  = TaxInvoice::where('customer_id', $customerId)->where('invoice_date', $date)->where('invoice_status','<>','Cancelled')->sum('net_amt');
        $bulkInvAmt = BulkMilkOrder::where('customer_id', $customerId)->where('invoice_date', $date)->where('invoice_status','<>','Cancelled')->sum('net_amt');

        $invoiceAmount = $salInvAmt + $taxInvAmt + $bulkInvAmt;
    
        if($invoiceAmount == 0)
            return $invoiceAmount;

        $cash      = $this->getReceiptAmount($customerId, $date, 'Cash');
        $bank      = $this->getReceiptAmount($customerId, $date, 'Bank');
        $incentive = $this->getReceiptAmount($customerId, $date, 'Incentive');
        $deposit   = $this->getReceiptAmount($customerId, $date, 'Deposit');
        $others    = $this->getReturnAmount($customerId, $date);

        $received  = $cash + $bank + $incentive + $deposit + $others;
        
        return $invoiceAmount - $received;
    }
}
