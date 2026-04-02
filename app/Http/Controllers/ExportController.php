<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ReportController;
use App\Exports\EnquiryExport;
use App\Exports\AttendanceExport;
use App\Exports\ItemwiseSalesExport;
use App\Exports\HSNwiseSummaryExport;
use App\Exports\TaxwiseSummaryExport;
use App\Exports\statementStyleCustomerExport;
use App\Exports\accountStyleCustomerExport;
use App\Exports\transactionSummaryExport;
use App\Exports\InvoiceReportExport;
use App\Exports\customerWiseItemExport;
use App\Exports\BusinessWiseExport;
use App\Models\Transactions\Enquiry;
use App\Models\Transactions\Followup;
use App\Models\Profiles\Employee;
use App\Models\Places\Area;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Places\MRoute;
use App\Models\Profiles\Customer;
class ExportController extends Controller
{
    private $reportController;

    public function __construct() 
    {
        $this->middleware('auth');
        $this->reportController = new ReportController;
    }

    public function enquiryExport(Request $request) 
    {
        $fromDate   = $request->input('fromDate');
        $toDate     = $request->input('toDate');
        $empId      = $request->input('empId');
        $areaId     = $request->input('areaId');

        $enquiries  = Enquiry::select('id','shop_name','area_name','contact_num','enq_datetime','emp_id','conversion_status')
                            ->with('employee:id,name')
                            ->whereBetween('enq_datetime',[$fromDate." 00:00:00",$toDate." 23:59:59"])
                            ->when($empId<>"0", function($query) use($empId) { return $query->where('emp_id', $empId); })
                            ->when($areaId<>"0", function($query) use($areaId) { return $query->where('area_id', $areaId); })
                            ->orderBy('enq_datetime')
                            ->get();

        $enquiryData = [];
        for($i=0; $i<count($enquiries); $i++) {
            $col = 0;
            $enquiryData[$i][$col++] = $i+1;
            $enquiryData[$i][$col++] = displayDate($enquiries[$i]->enq_datetime);
            $enquiryData[$i][$col++] = $enquiries[$i]->employee->name;
            $enquiryData[$i][$col++] = $enquiries[$i]->area_name;
            $enquiryData[$i][$col++] = $enquiries[$i]->shop_name;
            $enquiryData[$i][$col++] = $enquiries[$i]->contact_num;

            $followups = Followup::where('enquiry_id',$enquiries[$i]->id)->get();
            $enquiryData[$i][$col++] = count($followups);
            $enquiryData[$i][$col++] = $enquiries[$i]->conversion_status;
        }                
        
        $titles = ["S.No","Date","Employee","Area","Shop Name","Contact Number","Followups","Status"];
        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Enquiry Report", "Enquiry_Report_");
        $heading = $names['heading'];
        $fileName = $names['fileName'];

        if($empId<>"0") {
            $employee = Employee::where('id',$empId)->get('name')->first();
            $fileName .= "_" . str_replace(" ","-",$employee->name);
            $heading .= " by " . $employee->name;
        }
        if($areaId<>"0") {
            $area = Area::where('id',$areaId)->get('name')->first();
            $fileName .= "_" . str_replace(" ","-",$area->name);
            $heading .= " in " . $area->name;
        }
        $fileName .= ".xlsx";
        
        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate' => $toDate,
        //     'empId' => $empId,
        //     'areaId' => $areaId,
        //     'fileName' => $fileName,
        //     'heading' => $heading,
        //     'titles' => $titles,
        //     'data' => $enquiryData,
        // ]);

        $enquiryExport = new EnquiryExport($heading, $titles, $enquiryData);
        $response = Excel::download($enquiryExport, $fileName);
        ob_end_clean();
        return $response;
    }

    public function attendanceExport(Request $request) 
    {
        $fromDate   = $request->input('fromDate');
        $toDate     = $request->input('toDate');
        $empId      = $request->input('empId');

        $attendanceData = $this->reportController->getAttendanceData($fromDate, $toDate, $empId);
        
        $titles = ["S.No","Employee","Code","Date","Time In","Time Out","Time In","Time Out","Elapsed Time [Session]"];
        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Attendance Report", "Attendance_Report_");
        $heading = $names['heading'];
        $fileName = $names['fileName'];

        if($empId<>"0") {
            $employee = Employee::where('id',$empId)->get('name')->first();
            $fileName .= "_" . str_replace(" ","-",$employee->name);
            $heading .= " of " . $employee->name;
        }
        $fileName .= ".xlsx";
        
        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate' => $toDate,
        //     'empId' => $empId,
        //     'fileName' => $fileName,
        //     'heading' => $heading,
        //     'titles' => $titles,
        //     'data' => $attendanceData,
        // ]);

        $attendanceExport = new AttendanceExport($heading, $titles, $attendanceData);
        $response = Excel::download($attendanceExport, $fileName);
        ob_end_clean();
        return $response;
    }

    public function itemWiseSalesExport(Request $request) 
    {
        $fromDate = $request->input('fromDate');
        $toDate   = $request->input('toDate');
        $type     = $request->input('type');

        $salesData = $this->reportController->getItemWiseSalesData($fromDate, $toDate, $type);
        
        // Determine titles based on the report type
        $titles = match ($type) {
            'Count' => ["S.No","Item","Damage Qty","Spoilage Qty","Sample Qty","Ghee Qty","Qty","Total Qty","Type"],
            'Amount' => ["S.No","Item","Damage Amount","Spoilage Amount","Sample Amount","Ghee Amount","Amount","Total Amount","Type"],
            default => ["S.No","Item","Damage Qty","Damage Amt","Spoilage Qty","Spoilage Amt","Sample Qty","Sample Amt","Ghee Qty","Ghee Amt","Qty","Amount","Total Qty","Total Amount","Type"]
        };

        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Item wise Sales Report", "Item_wise_Sales_Report_");
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate'   => $toDate,
        //     'reportType'=> $type,
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $salesData,
        // ]);

        // Export the report and return as download
        $reportExport = new ItemwiseSalesExport($heading, $titles, $salesData);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }

    public function hsnWiseSalesExport(Request $request) 
    {
        $fromDate = $request->input('fromDate');
        $toDate   = $request->input('toDate');
        $type     = $request->input('type');

        if($type == "Format1") {
            $titles = ["S.No","HSN/SAC","Description","Type of\nSupply","UOM","Total\nQuantity","Total\nValue","Tax Rate","Taxable\nAmount","Integrated Tax\nAmount","Central Tax\nAmount","State Tax\nAmount","Cess\nAmount","Total Tax\nAmount"];
            $rows = $this->reportController->getHsnWiseDataByProduct($fromDate, $toDate);
            $data = $rows["records"];
            $totals = $rows["totals"];
            $data[] = ['Grand Total', '','','','', $totals["total_qty"], $totals["total_value"], '', $totals["taxable_amt"], $totals["igst"], $totals["cgst"], $totals["sgst"], '', $totals["total_tax"]];
            $names = $this->generateHeadingAndFileName($fromDate, $toDate, "HSN wise Summary", "HSN_wise_Summary_");
        }
        else {
            $titles = ["S.No","HSN Name","HSN Code","PSalesQty","BSalesQty","TxSalesQty","Total Qty","MilkAmt","TSAMT","SGST","CGST","IGST","TaxSalesAmt","BulkSalesAmt","Total Sales Amt"];
            $rows = $this->reportController->getHsnWiseDataByName($fromDate, $toDate);
            $data = $rows["records"];
            $totals = $rows["totals"];
            $data[] = ['Grand Total', '','', $totals["pouch_qty"], $totals["bulk_qty"], $totals["tax_qty"], $totals["total_qty"], $totals["pouch_sales_amt"], $totals["taxable_amt"], $totals["sgst"], $totals["cgst"], $totals["igst"], $totals["tax_sales_amt"], $totals["bulk_sales_amt"], $totals["total_sales_amt"]];
            $names = $this->generateHeadingAndFileName($fromDate, $toDate, "HSN Name wise Summary", "HSN_Name_wise_Summary_");
        }
        
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate'   => $toDate,
        //     'reportType'=> $type,
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $data,
        // ]);

        // Export the report and return as download
        $reportExport = new HSNwiseSummaryExport($heading, $titles, $type, $data);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }    

    public function taxWiseSalesExport(Request $request) 
    {
        $fromDate = $request->input('fromDate');
        $toDate   = $request->input('toDate');
        $type     = $request->input('type');

        if($type == "Format1") {
            $titles = ["S.No","HSN Name","HSN Code","Tax Rate","Taxable Amount","SGST","CGST","IGST","Tax Amount","Total Amount"];
            $rows = $this->reportController->getTaxWiseDataFormat1($fromDate, $toDate);            
            $data = $rows["records"];
            $totals = $rows["totals"];
            $data[] = ['Grand Total', '','','', $totals["taxable_amt"], $totals["sgst"], $totals["cgst"], $totals["igst"], $totals["tax_amt"], $totals["total_amt"]];
            $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Tax wise Summary", "Tax_wise_Summary_");
        }
        else {
            $titles = ["S.No","Tax Rate","Taxable Amount","SGST","CGST","IGST","Tax Amount","Total Amount"];
            $rows = $this->reportController->getTaxWiseDataFormat2($fromDate, $toDate);
            $data = $rows["records"];
            $totals = $rows["totals"];
            $data[] = ['Grand Total', '', $totals["taxable_amt"], $totals["sgst"], $totals["cgst"], $totals["igst"], $totals["tax_amt"], $totals["total_amt"]];
            $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Tax Rate wise Summary", "Tax_Rate_wise_Summary_");
        }
        
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate'   => $toDate,
        //     'reportType'=> $type,
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $data,
        // ]);

        // Export the report and return as download
        $reportExport = new TaxwiseSummaryExport($heading, $titles, $type, $data);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    } 

    public function statementStyleCustomerExport(Request $request) 
    {
        $customerId = $request->input('customer_id') ?? 0;
        $fromDate = $request->input('from_date') ?? date('Y-m-d');
        $toDate = $request->input('to_date') ?? date('Y-m-d');
        $row = $this->reportController->getCustomerReportData($customerId, $fromDate, $toDate);    
        // dd($row);    
        // Titles
        $titles = ["Date", "Invoice No.", "Qty", "Inv Amt", "Inv Tot Amt", "Cash", "Bank", "Incentive", "Deposit", "Returns", "Discount", "Balance"];  
        $data = []; 

        // Populate records
        foreach ($row['records'] as $index => $record) {
            $col = 0;
            $data[$index][$col++] = $record['date'] ?? '';
            // Invoice numbers (one per line)
            $data[$index][$col++] = !empty($record['invoices']) 
                ? implode(", ", array_column($record['invoices'], 'invoice_num')) 
                : '';

            // Qty (one per line)
            $data[$index][$col++] = !empty($record['invoices']) 
                ? implode(", ", array_map(function($invoice) {
                    return getTwoDigitPrecision($invoice['qty']); // Ensure 2 decimal places
                }, $record['invoices']))
                : 0;

            // Net Amount (one per line)
            $data[$index][$col++] = !empty($record['invoices']) 
                ? implode(", ", array_map(function($invoice) {
                    return getTwoDigitPrecision($invoice['net_amt']); // Ensure 2 decimal places
                }, $record['invoices']))
                : 0;
            $data[$index][$col++] = getTwoDigitPrecision($record['invoiceTotal'] ?? 0);
            $data[$index][$col++] = getTwoDigitPrecision($record['cash'] ?? 0);
            $data[$index][$col++] = ($record['bank'] != 0) 
                ? implode(", ", array_map(function($bankRecord) {
                    return getTwoDigitPrecision($bankRecord['amount']); // Ensure 2 decimal places
                }, $record['bankRecords']->toArray())) // Convert Collection to Array
                : getTwoDigitPrecision($record['bank']);
            $data[$index][$col++] = getTwoDigitPrecision($record['incentive'] ?? 0);
            $data[$index][$col++] = getTwoDigitPrecision($record['deposit'] ?? 0);
            $data[$index][$col++] = getTwoDigitPrecision($record['returns'] ?? 0);
            $data[$index][$col++] = getTwoDigitPrecision($record['discount'] ?? 0);
            $data[$index][$col++] = getTwoDigitPrecision($record['balance'] ?? 0);
        }
        // Grand Total Row
        $totals = $row["totals"] ?? ["qty" => 0, "invoice" => 0, "invoice_tot" => 0, "cash" => 0, "bank" => 0, "incentive" => 0, "deposit" => 0, "discount" => 0, "returns" => 0];
        $data[] = [
            'Grand Total', '', 
            getTwoDigitPrecision($totals["qty"] ?? 0),
            getTwoDigitPrecision($totals["invoice"] ?? 0),
            getTwoDigitPrecision($totals["invoice"] ?? 0),
            getTwoDigitPrecision($totals["cash"] ?? 0),
            getTwoDigitPrecision($totals["bank"] ?? 0),
            getTwoDigitPrecision($totals["incentive"] ?? 0),
            getTwoDigitPrecision($totals["deposit"] ?? 0),
            getTwoDigitPrecision($totals["returns"] ?? 0),
            getTwoDigitPrecision($totals["discount"] ?? 0),
            getTwoDigitPrecision($record['balance'] ?? 0)
        ];

       // Summary Section
       // Add an empty row for spacing between Grand Total and Summary
        $data[] = ["", "", "", "", "", "", "", "", "", "", ""];

        $summary = $row['summary'] ?? ["opening" => 0.00, "invoices" => 0, "receipts" => 0, "returns" => 0, "closing" => 0];        
        $data[] = ["Summary", "", "", ""];
        $data[] = ["Opening Balance","", getTwoDigitPrecision($summary['opening'] ?? 0.00)];
        $data[] = ["Total Invoice Amount", "", getTwoDigitPrecision($summary['invoices'] ?? 0)];
        $data[] = ["Total Receipt Amount", "", getTwoDigitPrecision($summary['receipts'] ?? 0)];
        $data[] = ["Total Return Amount", "", getTwoDigitPrecision($summary['returns'] ?? 0)];
        $data[] = ["Total Discount Amount", "", getTwoDigitPrecision($summary['discount'] ?? 0)];
        $data[] = ["Closing Balance", "", getTwoDigitPrecision($summary['closing'] ?? 0)];


        // Excel Export
        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Customer Statement Report", "Customer_Statement_Report_");
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";
        $reportExport = new statementStyleCustomerExport($heading, $titles, $data);
        ob_end_clean();
        return Excel::download($reportExport, $fileName);
    }

    public function customerAccountExport(Request $request) 
    {
        $fromDate = $request->input('fromDate');
        $toDate   = $request->input('toDate');
        $customerId = $request->input('customerId');

        $customerData = $this->reportController->getAccountStyleCustomerData($customerId, $fromDate, $toDate);
        // dd($customerData);
        // Determine titles based on the report type
        $titles = ["Date","Particulars","Vch Type","Vch No","Debit","Credit"];

        $data = [];
        $total = $customerData['totals'];
        $opening = [
            'date'   => $customerData['balances']['Opening Balance']['date'] ?? '',
            'debit'  => $customerData['balances']['Opening Balance']['debit'] ?? '',
            'credit' => $customerData['balances']['Opening Balance']['credit'] ?? ''
        ];
        $closing = [
            'date'   => $customerData['balances']['Closing Balance']['date'] ?? '',
            'debit'  => $customerData['balances']['Closing Balance']['debit'] ?? '',
            'credit' => $customerData['balances']['Closing Balance']['credit'] ?? ''
        ];       

        $data[] = [$opening['date'],($opening['debit'] ? "Cr   " : "Dr   ")."Opening Balance","","",$opening['debit'],$opening['credit']];
       
        foreach ($customerData['records'] as $index => $record) {
            $col = 0;
            $data[$index + 1][$col++] = $record['date'];          // Date
            $data[$index + 1][$col++] = $record['particulars'];   // Particulars
            $data[$index + 1][$col++] = $record['vtype'];         // Voucher Type
            $data[$index + 1][$col++] = $record['vnum'];          // Voucher No
            $data[$index + 1][$col++] = $record['debit']; // Debit Amount (Ensure default 0.00)
            $data[$index + 1][$col++] = $record['credit']; // Credit Amount (Ensure default 0.00)
        }
        $data[] = ["","","","",$total['debit'],$total['credit']];
        $data[] = [$closing['date'],($closing['debit'] ? "Cr   " : "Dr  ") ."Closing Balance","","",$closing['debit'],$closing['credit']];
        $data[] = ["","","","",$total['total'],$total['total']];
             
        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Customer Account Style Report", "Customer_account_Atyle_Report_");
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate'   => $toDate,
        //     'customerId'=>$customerId,
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $customerData,
        // ]);

        // Export the report and return as download
        $reportExport = new accountStyleCustomerExport($heading, $titles, $data);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }

    public function transactionExport(Request $request) 
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $type   = $request->input('type'); 
        $titles = ["S.No","Date","Type","Number","Code","Customer","Debit","Credit"];
        $rows = $this->reportController->transactionData($fromDate, $toDate, $type);
        $data = $rows["data"];
        $totals = $rows["totals"];
        // Add Serial Numbers
        $serialNumber = 1;
        foreach ($data as &$row) {
            array_unshift($row, $serialNumber++); // Insert serial number at the beginning of each row
        }        
        $data[] = ['Total', '','','','','', $totals["debit"], $totals["credit"]];
        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Transaction Report", "Transaction_Report_");      
        
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate'   => $toDate,
        //     'reportType'=> $type,
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $data,
        // ]);

        // Export the report and return as download
        $reportExport = new transactionSummaryExport($heading, $titles, $data);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }   

    public function invoiceExport(Request $request)
    {
        $fromDate = $request->query('fromDate');
        $toDate = $request->query('toDate');
        $routeId = $request->query('route');

        $names = $this->generateHeadingAndFileName($fromDate, $toDate, "Invoice Report", "Invoice_Report_");      
        
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        return Excel::download(new InvoiceReportExport($fromDate, $toDate, $routeId, $heading), $fileName);
    }

    public function itemWiseCustomerExport(Request $request)
    {
        $fromDate = $request->input('fromDate');
        $toDate   = $request->input('toDate');
        $productId = $request->input('productId');
        $productName = $request->input('productName');

        $data = $this->reportController->getItemWiseCustomerData($fromDate, $toDate, $productId);
        $serialNumber = 1;
        $lastIndex = count($data) - 1;
        
        foreach ($data as $index => &$row) {
            if ($index !== $lastIndex) { // Skip adding serial number for the last row
                array_unshift($row, $serialNumber++);
            } else {
                array_unshift($row, "Grand Total"); // Empty serial number for the last row
            }
        }
        
        // Determine titles based on the report type
        $titles = ["S.No","Invoice Date","Invoice No","Customer","Route","Category","Qty","Amount"];
        $names = $this->generateHeadingAndFileNameWithProduct($fromDate, $toDate, "Item wise Customer Report", "Item_wise_Customer_Report_",$productName);
     
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";

        // return response()->json([
        //     'fromDate' => $fromDate,
        //     'toDate'   => $toDate,
        //     'reportType'=> $type,
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $salesData,
        // ]);

        // Export the report and return as download
        $reportExport = new transactionSummaryExport($heading, $titles, $data);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }

    public function customerWiseItemExport(Request $request) 
    {
        $customerId = $request->input('customerId') ?? 0;
        $fromDate = $request->input('fromDate') ?? date('Y-m-d');
        $toDate = $request->input('toDate') ?? date('Y-m-d');
        $customer = Customer::find($customerId);
    
        $titles = ["S.No","Invoice Date","Invoice No","Product","Category","Qty","Amount"];
        $rows = $this->reportController->getCustomerWiseItemData($fromDate, $toDate, $customerId);          
        $data = $rows["data"];   
        $tableOne = $rows["data"];      
        $totals = $rows["totals"];
        $summary = $rows['product_summary'];
        $totalQty = 0;
    
        // Add Serial Numbers
        $serialNumber = 1;
        foreach ($data as &$row) {
            array_unshift($row, $serialNumber++);
        }        
    
        // Append total row
        $data[] = ['Total', '','','','', $totals["qty"], $totals["amount"]];
        $data[] = ['','','','','','',''];        
        $data[] = ['Product Summary',"",""];        
    
        // Append summary data
        foreach ($summary as $index => $qty) {
            $totalQty += $qty['qty']; // ✅ Fix: Access 'qty' inside array
            $data[] = [$index,"", $qty['qty']]; // ✅ Fix: Only include 'qty' value
        }        
    
        $data[] = ['Total',"", $totalQty];        
    
        // Generate filename and heading
        $names = $this->generateHeadingAndFileNameWithProduct($fromDate, $toDate, "Customer wise Item Report", "Customer_Wise_Item_Report_",$customer->customer_name);  
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";
    
        // Export the report
        // $reportExport = new customerWiseItemExport($heading, $titles, $data, $tableOne);
        // return Excel::download($reportExport, $fileName);
        $reportExport = new customerWiseItemExport($heading, $titles, $data, $tableOne);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }   
    
    public function businessWiseExport(Request $request) 
    {
        $data = $this->reportController->businessWiseExportSupport($request);
                
        $titles = match ($data['report_type']) {
            'Itemized' => ["S.No","HSN Code","Item Name","Unit","Qty","Tax Rate","Taxable Amt","SGST","CGST","IGST","Tax Amt","Net Amt"],
            'HSN-based' => ["S.No","HSN Name","HSN Code","Total Qty","Tax Rate","Taxable Amt","SGST","CGST","IGST","Tax Amt","Net Amt"],
        };

        $reportTitle = $data['business_type'] . " " . ($data['report_type'] == "Both" ? "B2B B2C" : $data['report_type']) . " Report";
        $fileTitle = $data['business_type'] . "_" . ($data['report_type'] == "Both" ? "B2B_B2C" : $data['report_type']) . "_Report_";

        $names = $this->generateHeadingAndFileName($data['dates']['from'], $data['dates']['to'], $reportTitle, $fileTitle);
        $heading = $names['heading'];
        $fileName = $names['fileName'].".xlsx";
        $records = [];
        $totals = $data['totals'];

        if($data['report_type'] == "Itemized") {
            foreach($data['records'] as $record) {
                $records[] = [$record['sno'], $record['hsn_code'], $record['product'], $record['uqc'], $record['total_qty'], 
                    $record['tax_rate'], $record['taxable_amt'], $record['sgst'], $record['cgst'], $record['igst'], $record['total_tax'], $record['total_value']];
            }
            $records[] = ["Grand Total","","","",$totals['total_qty'],"",$totals['taxable_amt'],$totals['sgst'],$totals['cgst'],$totals['igst'],$totals['total_tax'],$totals['total_value']];
        }
        else {
            foreach($data['records'] as $record) {
                $records[] = [$record['sno'], $record['hsn_name'], $record['hsn_code'], $record['qty'],
                    $record['tax_rate'], $record['taxable_amt'], $record['sgst'], $record['cgst'], $record['igst'], $record['tax_amt'], $record['net_amt']];
            }
            $records[] = ["Grand Total","","",$totals['qty'],"",$totals['taxable_amt'],$totals['sgst'],$totals['cgst'],$totals['igst'],$totals['tax_amt'],$totals['net_amt']];
        }

        // $records[] = $data['totals'];
        // return response()->json([
        //     'fromDate' => $data['dates']['from'],
        //     'toDate'   => $data['dates']['to'],
        //     'reportType'=> $data['report_type'],
        //     'businessType'=> $data['business_type'],
        //     'fileName' => $fileName,
        //     'heading'  => $heading,
        //     'titles'   => $titles,
        //     'data'     => $records,
        // ]);

        // Export the report and return as download
        $reportExport = new BusinessWiseExport($heading, $titles, $records);
        $response = Excel::download($reportExport, $fileName);
        ob_end_clean(); // Clear the output buffer
        return $response;
    }

    private function generateHeadingAndFileName($fromDate, $toDate, $heading, $fileName) {
        // Convert the dates to DateTime objects for easier manipulation
        $fromDateTime = new \DateTime($fromDate);
        $toDateTime = new \DateTime($toDate);

        // Generate heading
        if ($fromDateTime == $toDateTime) { // Check if the dates are the same
            $heading .= " for " . $fromDateTime->format('d-m-Y');
        } 
        else {
            // Check if $fromDate is the 1st and $toDate is the last day of the month
            if ($fromDateTime->format('d') == '01' && $toDateTime->format('d') == $toDateTime->format('t')) {
                $heading .= " for " . $fromDateTime->format('F Y');
            } 
            else {
                $heading .= " dated from " . $fromDateTime->format('d-m-Y') . " to " . $toDateTime->format('d-m-Y');
            }
        }

        // Generate filename
        // Check if $fromDate is the 1st and $toDate is the last day of the month
        if ($fromDateTime->format('d') == '01' && $toDateTime->format('d') == $toDateTime->format('t')) {
            $fileName .= $fromDateTime->format('F_Y');
        } 
        else {
            $fileName .= $fromDateTime->format('d-m-Y');
            if ($fromDate !== $toDate) {
                $fileName .= "_" . $toDateTime->format('d-m-Y');
            }
        }

        return [
            'heading' => $heading,
            'fileName' => $fileName,
        ];
    }

    private function generateHeadingAndFileNameWithProduct($fromDate, $toDate, $heading, $fileName, $productName) {
        // Convert the dates to DateTime objects for easier manipulation
        $fromDateTime = new \DateTime($fromDate);
        $toDateTime = new \DateTime($toDate);
    
        // Append product name to heading
        if (!empty($productName)) {
            $heading .= " for " . $productName;
        }
    
        // Generate heading with date
        if ($fromDateTime == $toDateTime) {
            $heading .= " on " . $fromDateTime->format('d-m-Y');
        } else {
            if ($fromDateTime->format('d') == '01' && $toDateTime->format('d') == $toDateTime->format('t')) {
                $heading .= " for " . $fromDateTime->format('F Y');
            } else {
                $heading .= " dated from " . $fromDateTime->format('d-m-Y') . " to " . $toDateTime->format('d-m-Y');
            }
        }
    
        // Append product name to file name
        if (!empty($productName)) {
            $fileName .= str_replace(' ', '_', $productName) . "_";
        }
    
        // Generate filename with date
        if ($fromDateTime->format('d') == '01' && $toDateTime->format('d') == $toDateTime->format('t')) {
            $fileName .= $fromDateTime->format('F_Y');
        } else {
            $fileName .= $fromDateTime->format('d-m-Y');
            if ($fromDate !== $toDate) {
                $fileName .= "_" . $toDateTime->format('d-m-Y');
            }
        }
    
        return [
            'heading' => $heading,
            'fileName' => $fileName,
        ];
    }
    
}
