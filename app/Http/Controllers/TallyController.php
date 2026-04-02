<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use SimpleXMLElement;
use App\Models\Orders\Order;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Orders\SalesInvoiceItem;
use App\Models\Orders\TaxInvoiceItem;
use App\Models\Orders\BulkMilkOrderItem;
use App\Models\Places\MRoute;
use App\Models\Places\Address;
use App\Models\Profiles\Customer;
use App\Models\Products\Product;
use App\Models\Products\ProductGroup;
use App\Models\Products\ProductUnit;
use App\Models\Products\UOM;
use App\Models\Masters\Setting;

class TallyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    // Laravel <-> Tally
    public function sendDataToTally()
    {
        // return $this->sendDataToTallyV1();
        return $this->sendDataToTallyV2();
    }

    // Laravel <-> Tally
    public function receiveDataFromTally()
    {
        // return $this->receiveDataFromTallyV1();
        return $this->receiveDataFromTallyV2();
    }

    // Browser URL
    public function downloadXml()
    {
        return $this->downloadXmlV1();
        // return $this->downloadXmlV2();
        // return $this->downloadXmlV1_2();
        // return $this->downloadXmlV2_2();
    }

    // Laravel <-> jQuery <-> Tally (or)
    // Laravel <-> jQuery <-> Flask <-> Tally
    public function automateXml()
    {
        return $this->funcAutomateXml();
    }

    public function createTallySync()
    {
        return view('transactions.tally.tally-sync');
    }

    public function saveXml(Request $request)
    {
        return $this->funcSaveXml($request);
    }


/* Send and Receive XML data to Tally ERP using HTTP POST request */
/* -------------------------------------------------------------- */
    private function sendDataToTallyV1()
    {
        $xml = $this->getSenderXml();

        try {
            $response = Http::withHeaders(['Content-Type' => 'text/xml'])
                            // ->post('http://localhost:9000/', $xml);
                            ->post('http://192.168.31.205:9000/', $xml);

            return $response->body();
        } 
        catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    private function receiveDataFromTallyV1()
    {
        $xml = $this->getReceiverXml();

        try {
            $response = Http::withHeaders(['Content-Type' => 'text/xml'])
                            // ->post('http://localhost:9000/', $xml);
                            ->post('http://192.168.31.205:9000/', $xml);
                            
            return simplexml_load_string($response->body());
        }
        catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }
/* --------------------------------------------------------------- */


/* Send and Receive XML data to Tally ERP using Guzzle HTTP client */
/* --------------------------------------------------------------- */
    private function sendDataToTallyV2()
    {
        $xml = $this->getSenderXml();

        // Create a Guzzle HTTP client
        $client = new Client();

        // Send the XML content to Tally running on localhost:9000
        try {
            $response = $client->post('http://localhost:9000', [
                'headers' => ['Content-Type' => 'application/xml'],
                'body' => $xml,
            ]);

            // Check the response status
            if ($response->getStatusCode() == 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data sent to Tally successfully',
                    'data'    => $response->getBody()->getContents(),
                ]);
            }
        } 
        catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send data to Tally',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    private function receiveDataFromTallyV2()
    {
        $xml = $this->getReceiverXml();

        // Create a Guzzle HTTP client
        $client = new Client();

        // Send the XML content to Tally running on localhost:9000
        try {
            $response = $client->post('http://localhost:9000', [
                'headers' => ['Content-Type' => 'application/xml'],
                'body' => $xml,
            ]);

            // Check the response status
            if ($response->getStatusCode() == 200) {
                echo $response->getBody()->getContents();
            }
        }
        catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send data to Tally',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }
/* -------------------------------------------------------------- */


/* -------------------- Generate XML Content -------------------- */
/* -------------------------------------------------------------- */
    private function getSenderXml() // generateXMLV1() : send data 
    {
        return '<ENVELOPE>
                    <HEADER>
                        <TALLYREQUEST>Import Data</TALLYREQUEST>
                    </HEADER>
                    <BODY>
                        <IMPORTDATA>
                            <REQUESTDESC>
                                <REPORTNAME>Vouchers</REPORTNAME>
                            </REQUESTDESC>
                            <REQUESTDATA>
                                <TALLYMESSAGE xmlns:UDF="TallyUDF">
                                    <VOUCHER VCHTYPE="Sales" ACTION="Create">
                                        <DATE>20250310</DATE>
                                        <PARTYNAME>Tester</PARTYNAME>
                                        <AMOUNT>1000.00</AMOUNT>
                                        <ALLLEDGERENTRIES.LIST>
                                            <LEDGERNAME>Sales Account</LEDGERNAME>
                                            <AMOUNT>-1000.00</AMOUNT>
                                        </ALLLEDGERENTRIES.LIST>
                                    </VOUCHER>
                                </TALLYMESSAGE>
                            </REQUESTDATA>
                        </IMPORTDATA>
                    </BODY>
                </ENVELOPE>';
    }

    private function getReceiverXml() // generateXMLV1() : receive data
    {
        return '<ENVELOPE>
                    <HEADER>
                        <TALLYREQUEST>Export Data</TALLYREQUEST>
                    </HEADER>
                    <BODY>
                        <EXPORTDATA>
                            <REQUESTDESC>
                                <REPORTNAME>List of Accounts</REPORTNAME>
                            </REQUESTDESC>
                        </EXPORTDATA>
                    </BODY>
                </ENVELOPE>';
    }

    private function generateXmlV2()
    {
        // Define XML structure
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ENVELOPE></ENVELOPE>');

        // Add HEADER
        $header = $xml->addChild('HEADER');
        $header->addChild('TALLYREQUEST', 'Import Data');

        // Add BODY
        $body = $xml->addChild('BODY');
        $importData = $body->addChild('IMPORTDATA');
        $requestDesc = $importData->addChild('REQUESTDESC');
        $requestDesc->addChild('REPORTNAME', 'Vouchers');

        $requestData = $importData->addChild('REQUESTDATA');

        // Create TALLYMESSAGE
        $tallyMessage = $requestData->addChild('TALLYMESSAGE');
        $tallyMessage->addAttribute('xmlns:UDF', 'TallyUDF');

        // Create VOUCHER
        $voucher = $tallyMessage->addChild('VOUCHER');
        $voucher->addAttribute('VCHTYPE', 'Sales');
        $voucher->addAttribute('ACTION', 'Create');

        // Voucher Details
        $voucher->addChild('DATE', '20250311'); // Format: YYYYMMDD
        $voucher->addChild('PARTYLEDGERNAME', 'Customer A');
        $voucher->addChild('VOUCHERTYPENAME', 'Sales');
        $voucher->addChild('VOUCHERNUMBER', '1001');

        // Ledger Entries - Customer
        $entry1 = $voucher->addChild('ALLLEDGERENTRIES.LIST');
        $entry1->addChild('LEDGERNAME', 'Customer A');
        $entry1->addChild('ISDEEMEDPOSITIVE', 'Yes');
        $entry1->addChild('AMOUNT', '-1000.00');

        // Ledger Entries - Sales
        $entry2 = $voucher->addChild('ALLLEDGERENTRIES.LIST');
        $entry2->addChild('LEDGERNAME', 'Sales');
        $entry2->addChild('ISDEEMEDPOSITIVE', 'No');
        $entry2->addChild('AMOUNT', '1000.00');

        // Save XML
        return $xml;
    }

    private function generateXmlV3()
    {
        $ledgers = [
            (object) [
                'name' => 'Customer A',
                'parent' => 'Sundry Debtors',
                'opening_balance' => '10000',
                'is_billwise_on' => true,
                'tax_classification' => '',
                'gst_applicable' => 'Yes',
                'gst_type' => 'Regular',
                'tax_type' => '',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'gstin' => '33AAAAA1234A1Z5'
            ],
            (object) [
                'name' => 'Supplier B',
                'parent' => 'Sundry Creditors',
                'opening_balance' => '5000',
                'is_billwise_on' => false,
                'tax_classification' => '',
                'gst_applicable' => 'No',
                'gst_type' => '',
                'tax_type' => '',
                'state' => 'Karnataka',
                'country' => 'India',
                'gstin' => ''
            ]
        ];

        // Render Blade template with ledger data
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim(view('transactions.tally.tally-export', compact('ledgers'))->render());

        // Return XML
        return $xmlContent;
    }
/* -------------------------------------------------------------- */


/* --------------------- Download XML --------------------------- */
/* -------------------------------------------------------------- */
    private function downloadXmlV1()
    {
        // Generate XML
        $xmlContent = $this->generateXmlV3();
        // $xmlContent = $this->getSenderXml();

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();

        // Return XML as a downloadable response
        return response()->make($xmlContent, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="tally_data.xml"',
        ]);
    }

    private function downloadXmlV2() // Store in File and Download
    {
        // Generate XML
        $xmlContent = $this->generateXmlV3();
        // $xmlContent = $this->getSenderXml();

        // Save XML File
        $fileName = 'tally_data.xml';
        $filePath = storage_path("app/public/{$fileName}");
        file_put_contents($filePath, $xmlContent);

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();
        
        // return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        return response()->download($filePath, $fileName);
    }

    private function downloadXmlV1_2()
    {
        // Generate XML (SimpleXMLElement object)
        $xml = $this->generateXmlV2();

        // Save as string
        $xmlContent = $xml->asXML();

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();

        // Return XML as a downloadable response
        return response()->make($xmlContent, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="tally_voucher.xml"',
        ]);        
    }    

    private function downloadXmlV2_2()
    {
        // Generate XML (SimpleXMLElement object)
        $xml = $this->generateXmlV2();
        
        // Save XML File
        $fileName = 'tally_voucher.xml';
        $filePath = storage_path("app/public/{$fileName}");
        $xml->asXML($filePath);

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();
        
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        // return response()->download($filePath, $fileName);
    }
/* -------------------------------------------------------------- */


/* --------------------- Save XML --------------------------- */
/* -------------------------------------------------------------- */
    private function funcSaveXml($request)
    {        
        // Validate the request
        $request->validate([
            'tally_response' => 'required|string',
        ]);

        // Get the XML response from the request
        $xmlContent = $request->input('tally_response');
        
        // Define file path
        $fileName = 'tally_response.xml';
        $filePath = storage_path("app/public/{$fileName}");

        // Save the XML response as a file
        file_put_contents($filePath, $xmlContent);

        return response()->json([
            'success' => true,
            'message' => 'Tally response saved successfully',
        ]);
    }
/* -------------------------------------------------------------- */

/* --------------------- Automate XML --------------------------- */
/* -------------------------------------------------------------- */
    private function funcAutomateXml()
    {
        // Generate XML
        $xmlContent = $this->getReceiverXml();
        // $xmlContent = $this->generateXmlV3();
        // $xmlContent = $this->generateXmlV2()->asXML();

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();
        
        // return response()->make($xmlContent, 200, [
        //     'Content-Type' => 'application/xml',
        // ]);

        return response($xmlContent)->header('Content-Type', 'application/xml');
    }
/* -------------------------------------------------------------- */

    public function tallyMasters()
    {
        $table = [];
        $routes = MRoute::select('id','name','tally_sync')->where('tally_sync', '<>', 'Synced')->orWhereNull('tally_sync')->get();        
        foreach($routes as $route) {
            $table[] = [
                'master'      => "Route",
                'id'          => $route->id,
                'description' => $route->name,
                'sync_status' => $route->tally_sync, 
            ];
        }

        $customers = Customer::select('id','customer_name','tally_sync')->where('tally_sync', '<>', 'Synced')->orWhereNull('tally_sync')->get();
        foreach($customers as $customer) {
            $table[] = [
                'master' => "Customer",
                'id'          => $customer->id,
                'description' => $customer->customer_name,
                'sync_status' => $customer->tally_sync, 
            ];
        }

        $groups = ProductGroup::select('id','name','tally_sync')->where('tally_sync', '<>', 'Synced')->orWhereNull('tally_sync')->get();
        foreach($groups as $group) {
            $table[] = [
                'master'      => "Product Group",
                'id'          => $group->id,
                'description' => $group->name,
                'sync_status' => $group->tally_sync, 
            ];
        }

        $units = UOM::select('id','unit_name','tally_sync')->where('tally_sync', '<>', 'Synced')->orWhereNull('tally_sync')->get();
        foreach($units as $unit) {
            $table[] = [
                'master'      => "Unit",
                'id'          => $unit->id,
                'description' => $unit->unit_name,
                'sync_status' => $unit->tally_sync, 
            ];
        }

        $products = Product::select('id','name','tally_sync')->where('tally_sync', '<>', 'Synced')->orWhereNull('tally_sync')->get();
        foreach($products as $product) {
            $table[] = [
                'master'      => "Product",
                'id'          => $product->id,
                'description' => $product->name,
                'sync_status' => $product->tally_sync, 
            ];
        }

        // return response()->json([
        return view('transactions.tally.tally-masters', [
            'table' => $table,
        ]);
    }

    public function tallyMaster(Request $request)
    {
        $master = $request->master;
        $id = $request->id;        

        if($master == "Route") {
            $groups = MRoute::select('name')->where('id',$id)->get();
            $xmlView = view('transactions.tally.masters.groups', compact('groups'))->render();
        }
        else if($master == "Customer") {
            $ledgers = Customer::select('id','customer_name','customer_code','route_id','address_lines','state','pincode','contact_num','gst_type','gst_number','pan_number')
                ->with('route:id,name')
                ->where('id',$id)                
                ->get();
            foreach($ledgers as &$ledger) {
                $ledger->gst_registration_type = str_contains($ledger->gst_type,"Unregistered") ? "Unregistered" : "Regular";
            }                        
            $xmlView = view('transactions.tally.masters.ledgers', compact('ledgers'))->render();
        }
        else if($master == "Unit") {
            $units = UOM::select('id','unit_name','display_name')->where('id',$id)->get();
            $xmlView = view('transactions.tally.masters.units', compact('units'))->render();
        }
        else if($master == "Product") {
            $items = Product::select('id','name','item_code','group_id','tax_type','hsn_code','gst','sgst','cgst','igst')
                ->with('prod_group:id,name')
                ->where('id',$id)
                ->get();
            
            foreach($items as &$item) {
                $item->taxability = $item->tax_type == "Taxable" ? "Taxable" : "Exempt";
                            
                $productUnits = ProductUnit::where('product_id',$item->id)
                    ->with('unit:id,display_name') 
                    ->orderByDesc('prim_unit')
                    ->get();
                $addtUnits = [];
                foreach($productUnits as $unit) {
                    if($unit->prim_unit == 1) {
                        $item->base_unit = $unit->unit->display_name;
                    }
                    else {
                        $addtUnits[] = [
                            'unit_name' => $unit->unit->display_name,
                            'conversion' => $unit->conversion
                        ];
                    }
                }
                $item->addt_units = json_encode($addtUnits);
            }
            $xmlView = view('transactions.tally.masters.stock-items', compact('items'))->render();
        }
        
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim($xmlView);

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();
        
        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    public function tallySyncMaster(Request $request)
    {
        try {
            $master = $request->master;
            $id = $request->id;

            $record = null;
            if ($master === "Route")
                $record = MRoute::where('id', $id)->first();
            else if ($master === "Customer")
                $record = Customer::where('id', $id)->first();
            else if ($master === "Product Group")
                $record = ProductGroup::where('id', $id)->first();
            else if ($master === "Unit")
                $record = UOM::where('id', $id)->first();
            else if ($master === "Product")
                $record = Product::where('id', $id)->first();

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => "Master Record Not Found!",
                ]);
            }

            $record->update(['tally_sync' => $request->sync_status]);

            return response()->json([
                'success' => true,
                'message' => "Master Synced Successfully!",
            ]);
        }
        catch(\Exception $ex){
            return response()->json([ 
                'success' => false,
                'message' => $ex->getMessage(), 
            ]);
        }
    }
 
    public function tallyInvoices(Request $request)
    {
        $date = $request->input('invoice_date', date('Y-m-d'));
        $type = $request->input('invoice_type', 'Pouch');
        $syncType = $request->input('sync_type', 'All');

        $models = [
            'Pouch' => SalesInvoice::class,
            'Tax' => TaxInvoice::class,
            'BulkMilk' => BulkMilkOrder::class,
        ];

        $invoices = $models[$type]::select('id', 'invoice_num', 'invoice_date', 'customer_name', 'route_name', 'tally_sync')
            ->where('invoice_date', $date)
            ->where('invoice_status', 'Generated')
            ->when($syncType === "Unsynced", function ($query) {
                return $query->where(function ($q) {
                    $q->where('tally_sync', '<>', 'Synced')
                      ->orWhereNull('tally_sync'); // Handle NULL cases
                });
            })
            ->get();

        // return response()->json([
        return view('transactions.tally.tally-invoices', [
            'date'      => $date,
            'type'      => $type,
            'sync_type' => $syncType,
            'invoices'  => $invoices,
        ]);
    }

    public function tallyInvoice(Request $request)
    {
        $invoiceNum = $request->invoice_num;

        $settings = Setting::where('category', 'Invoice')->pluck('key', 'value'); 
        $invoiceType = $settings->first(function ($key, $value) use ($invoiceNum) {
            return Str::startsWith($invoiceNum, $value) ? $key : null;
        });

        if($invoiceType == "sales-invoice")
            $xmlContent = $this->tallySalesInvoice($invoiceNum);
        else if($invoiceType == "tax-invoice")
            $xmlContent = $this->tallyTaxInvoice($invoiceNum);
        else if($invoiceType == "bulk-milk")
            $xmlContent = $this->tallyBulkMilkInvoice($invoiceNum);

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();
        
        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    private function tallySalesInvoice($invoiceNum)
    {
        $record = SalesInvoice::select('id','invoice_date','invoice_num','customer_id','amount','tcs','discount','round_off','net_amt','created_at','order_num','order_dt','vehicle_num')
            ->with('customer:id,customer_name,customer_code,gst_type,gst_number')
            ->where('invoice_num',$invoiceNum)
            ->first();        

        $items = SalesInvoiceItem::select('id','product_id','product_name','item_category','hsn_code','qty','amount')
            ->where('invoice_num',$invoiceNum)
            ->get();
        
        $customer = [
            'name'       => $record->customer->customer_name,
            'code'       => $record->customer->customer_code,
            'gst_number' => $record->customer->gst_number,
        ];

        $addresses = $this->getAddresses($record->customer_id, $record->order_num);

        $voucher = (object) [
            'voucher_type'   => 'Milk Sales',
            'ledger'         => 'Milk Sales',
            'remote_id'      => $record->invoice_num . "-" . preg_replace('/\D/', '', $record->order_dt),
            'date'           => str_replace('-', '', $record->invoice_date),
            'date_time'      => getInvoiceDateTimeForTally($record->created_at),
            'invoice_number' => $record->invoice_num,
            'entered_by'     => 'Admin',
            'customer'       => $customer,
            'billing'        => $addresses['billing'],
            'delivery'       => $addresses['delivery'],
            'vehicle_number' => $record->vehicle_num,
            'amount'         => $record->amount,
            'tcs'            => $record->tcs,
            'discount'       => $record->discount,
            'round_off'      => $record->round_off,
            'net_amt'        => $record->net_amt,
            'registration_type' => str_contains($record->customer->gst_type, 'Unregistered') ? 'Unregistered/Consumer' : 'Regular',
            'gst_nature'     => ($addresses['delivery']['state'] === "Tamil Nadu") ? "Local Sales - Exempt" : "Interstate Sales - Exempt",            
        ];

        foreach($items as &$item) {
            $item->code = Product::where('id',$item->product_id)->value('item_code');
            if($item->item_category <> "Regular") {
                $item->code .= " " . $item->item_category;
            }

            $item->unit = $this->getPrimaryUnitName($item->product_id);
            $item->rate = getTwoDigitPrecision($item->amount / $item->qty) . "/" . $item->unit;
        }

        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlView = view('transactions.tally.masters.exempted-invoice', compact('voucher','items'));
        
        $xmlContent = $xmlDeclaration . trim($xmlView->render());        

        return $xmlContent;
    }

    private function tallyTaxInvoice($invoiceNumber)
    {        
        $record = TaxInvoice::select('id','invoice_date','invoice_num','customer_id','tot_amt','tax_amt','tcs','discount','round_off','net_amt','created_at','order_num','order_dt','vehicle_num')
            ->with('customer:id,customer_name,customer_code,gst_type,gst_number')
            ->where('invoice_num',$invoiceNumber)
            ->first();

        $items = TaxInvoiceItem::select('id','product_id','product_name','item_category','hsn_code','qty','amount','gst','sgst','cgst','igst')
            ->where('invoice_num',$invoiceNumber)
            ->get();

        $customer = [
            'name'       => $record->customer->customer_name,
            'code'       => $record->customer->customer_code,
            'gst_number' => $record->customer->gst_number,
        ];

        $addresses = $this->getAddresses($record->customer_id, $record->order_num);

        $voucher = (object) [
            'remote_id'      => $record->invoice_num . "-" . preg_replace('/\D/', '', $record->order_dt),
            'date'           => str_replace('-', '', $record->invoice_date),
            'date_time'      => getInvoiceDateTimeForTally($record->created_at),
            'invoice_number' => $record->invoice_num,
            'entered_by'     => 'Admin',
            'customer'       => $customer,
            'billing'        => $addresses['billing'],
            'delivery'       => $addresses['delivery'],
            'vehicle_number' => $record->vehicle_num,
            'tax_amt'        => $record->tax_amt,
            'tcs'            => $record->tcs,
            'discount'       => $record->discount,
            'round_off'      => $record->round_off,
            'net_amt'        => $record->net_amt,
            'registration_type' => str_contains($record->customer->gst_type, 'Unregistered') ? 'Unregistered/Consumer' : 'Regular',
            'gst_nature'     => ($addresses['delivery']['state'] === "Tamil Nadu") ? "Local Sales - Taxable" : "Interstate Sales - Taxable",
            'is_igst'        => !empty(optional($items->first())->igst),
        ];

        foreach($items as &$item) {
            $item->code = Product::where('id',$item->product_id)->value('item_code');
            if($item->item_category <> "Regular") {
                $item->code .= " " . $item->item_category;
            }

            $item->unit = $this->getPrimaryUnitName($item->product_id);
            $item->rate = getTwoDigitPrecision($item->amount / $item->qty) . "/" . $item->unit;            

            if($item->gst == 5)
                $item->ledger = "Sales @5%";
            else if($item->gst == 12)
                $item->ledger = "Sales 12%";
            else if($item->gst == 18)
                $item->ledger = "Sales 18%";
        }

        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlView = view('transactions.tally.masters.tax-invoice', compact('voucher','items'));
        $xmlContent = $xmlDeclaration . trim($xmlView->render());

        return $xmlContent;
    }

    private function tallyBulkMilkInvoice($invoiceNum)
    {
        $record = BulkMilkOrder::select('id','invoice_date','invoice_num','customer_id','tot_amt','tcs','round_off','net_amt','created_at','order_dt','vehicle_num')
            ->with('customer:id,customer_name,customer_code,gst_type,gst_number')
            ->where('invoice_num',$invoiceNum)
            ->first();

        $items = BulkMilkOrderItem::select('id','product_id','product_name','hsn_code','qty_ltr','amount')
            ->where('invoice_num',$invoiceNum)
            ->get();
        
        $customer = [
            'name'       => $record->customer->customer_name,
            'code'       => $record->customer->customer_code,
            'gst_number' => $record->customer->gst_number,
        ];

        $addresses = $this->getAddresses($record->customer_id, $record->invoice_num);

        $voucher = (object) [
            'voucher_type'   => 'Bulk Milk Sales',
            'ledger'         => 'Bulk Milk Sales',
            'remote_id'      => $record->invoice_num . "-" . preg_replace('/\D/', '', $record->order_dt),
            'date'           => str_replace('-', '', $record->invoice_date),
            'date_time'      => getInvoiceDateTimeForTally($record->created_at),
            'invoice_number' => $record->invoice_num,
            'entered_by'     => 'Admin',
            'customer'       => $customer,
            'billing'        => $addresses['billing'],
            'delivery'       => $addresses['delivery'],
            'vehicle_number' => $record->vehicle_num,
            'amount'         => $record->tot_amt,
            'tcs'            => $record->tcs,
            'discount'       => "",
            'round_off'      => $record->round_off,
            'net_amt'        => $record->net_amt,
            'registration_type' => str_contains($record->customer->gst_type, 'Unregistered') ? 'Unregistered/Consumer' : 'Regular',
            'gst_nature'     => ($addresses['delivery']['state'] === "Tamil Nadu") ? "Local Sales - Exempt" : "Interstate Sales - Exempt",            
        ];

        foreach($items as &$item) {                        
            $item->code = Product::where('id',$item->product_id)->value('item_code');
            $item->qty  = $item->qty_ltr;
            // $item->unit = $this->getPrimaryUnitName($item->product_id);
            $item->unit = "Ltrs";
            $item->rate = getTwoDigitPrecision($item->amount / $item->qty) . "/" . $item->unit;
        }

        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlView = view('transactions.tally.masters.exempted-invoice', compact('voucher','items'));
        $xmlContent = $xmlDeclaration . trim($xmlView->render());

        return $xmlContent;
    }

    private function getPrimaryUnitName($productId)
    {
        $unitId = ProductUnit::where('product_id',$productId)->where('prim_unit',1)->value('unit_id');
        $unitName = UOM::where('id',$unitId)->value('display_name');
        return $unitName;
    }

    public function tallySyncInvoice(Request $request)
    {
        try {
            $invoiceNum = $request->invoice_num;

            $settings = Setting::where('category', 'Invoice')->pluck('key', 'value'); 
            $invoiceType = $settings->first(function ($key, $value) use ($invoiceNum) {
                return Str::startsWith($invoiceNum, $value) ? $key : null;
            });

            $invoice = null;
            if ($invoiceType === "sales-invoice")
                $invoice = SalesInvoice::where('invoice_num', $invoiceNum)->first();
            else if ($invoiceType === "tax-invoice")
                $invoice = TaxInvoice::where('invoice_num', $invoiceNum)->first();
            else if ($invoiceType === "bulk-milk")
                $invoice = BulkMilkOrder::where('invoice_num', $invoiceNum)->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => "Invoice not found",
                ]);
            }

            $invoice->update(['tally_sync' => $request->sync_status]);

            return response()->json([
                'success' => true,
                'message' => "Synced",
            ]);
        }
        catch(\Exception $ex){
            return response()->json([ 
                'success' => false,
                'message' => $ex->getMessage(), 
            ]);
        }
    }

    public function tallyGroups()
    {
        // Generate XML
        $groups = MRoute::select('name')->get();
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim(view('transactions.tally.masters.groups', compact('groups'))->render());
        
        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();

        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    public function tallyLedgers()
    {        
        $ledgers = Customer::select('id','customer_name','customer_code','route_id','address_lines','state','pincode','contact_num','gst_type','gst_number','pan_number')
            ->with('route:id,name')
            ->where('status','Active')
            ->get();
        
        foreach($ledgers as &$ledger) {
            $ledger->gst_registration_type = str_contains($ledger->gst_type,"Unregistered") ? "Unregistered" : "Regular";
        }

        // Generate XML
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim(view('transactions.tally.masters.ledgers', compact('ledgers'))->render());
        
        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();

        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    public function tallyUnits()
    {        
        $units = UOM::select('id','unit_name','display_name')->get();        

        // Generate XML
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim(view('transactions.tally.masters.units', compact('units'))->render());
        
        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean(); 

        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    public function tallyStockGroups()
    {        
        // $groups = ProductGroup::select('id','name')->get();

        $groups = [
            (object) [
                'name' => 'Milk',
                'unit' => 'Ltr',
                'hsn_code' => '04012000',
                'taxability' => 'Exempt',
                'cgst' => '0',
                'sgst' => '0',
                'igst' => '0',               
            ],
            (object) [
                'name' => 'Curd',
                'unit' => 'Ltr',
                'hsn_code' => '04039090',
                'taxability' => 'Taxable',
                'cgst' => '2.5',
                'sgst' => '2.5',
                'igst' => '5',
            ],
            (object) [
                'name' => 'Ghee',
                'unit' => 'Ltr',
                'hsn_code' => '04059020',
                'taxability' => 'Taxable',
                'cgst' => '6',
                'sgst' => '6',
                'igst' => '12',
            ],
            (object) [
                'name' => 'Butter',
                'unit' => 'Nos',
                'hsn_code' => '04051000',
                'taxability' => 'Taxable',
                'cgst' => '6',
                'sgst' => '6',
                'igst' => '12',
            ],
            (object) [
                'name' => 'Buttermilk',
                'unit' => 'Ltr',
                'hsn_code' => '04039010',
                'taxability' => 'Taxable',
                'cgst' => '2.5',
                'sgst' => '2.5',
                'igst' => '5',
            ],
        ];

        // Generate XML
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim(view('transactions.tally.masters.stock-groups', compact('groups'))->render());
        
        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean(); 

        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    public function tallyStockItems()
    {        
        $items = Product::select('id','name','item_code','group_id','tax_type','hsn_code','gst','sgst','cgst','igst')
            ->with('prod_group:id,name')
            ->get();
        
        foreach($items as &$item) {            
            $item->taxability = $item->tax_type == "Taxable" ? "Taxable" : "Exempt";
                        
            $productUnits = ProductUnit::where('product_id',$item->id)
                ->with('unit:id,display_name') 
                ->orderByDesc('prim_unit')
                ->get();
            $addtUnits = [];
            foreach($productUnits as $unit) {
                if($unit->prim_unit == 1) {
                    $item->base_unit = $unit->unit->display_name;
                }
                else {
                    $addtUnits[] = [
                        'unit_name' => $unit->unit->display_name,
                        'conversion' => $unit->conversion
                    ];
                }
            }
            $item->addt_units = json_encode($addtUnits);
        }

        // Generate XML
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlContent = $xmlDeclaration . trim(view('transactions.tally.masters.stock-items', compact('items'))->render());
        
        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean(); 

        return response($xmlContent)->header('Content-Type', 'application/xml');
    }

    private function getAddresses($customerId, $orderNum): array
    {
        $addressData = $this->getAddressData($customerId, $orderNum);
        foreach (['billing', 'delivery'] as $type) {
            if ($addressData[$type]['state'] === "Tamilnadu") {
                $addressData[$type]['state'] = "Tamil Nadu";
            }
        }

        $formatAddress = function ($address) {
            return [
                'address_lines' => array_merge(
                    explode("\n", $address['address_lines']),
                    [
                        $address['district'],
                        $address['state'] . (!empty($address['pincode']) ? ' - ' . $address['pincode'] : '')
                    ]
                ),
                'district' => $address['district'],
                'state'    => $address['state'],
                'pincode'  => $address['pincode'],
            ];
        };

        return [
            'billing'  => $formatAddress($addressData['billing']),
            'delivery' => $formatAddress($addressData['delivery']),
        ];
    }

    public function getAddress() {
        return $this->getAddressData(261, '2526O-000001');
    }

    private function getAddressData($customerId, $orderNum) {        
        $customerAddress = Customer::select('address_lines','district','state','pincode')->where('id',$customerId)->first();
        $addressData = Order::where('order_num', $orderNum)->value('address_data');        
        if($addressData) {            
            $invoiceAddresses = json_decode($addressData, true)[0];
        }
        else {
            $data = BulkMilkOrder::where('invoice_num',$orderNum)->value('customer_data');
            $data = json_decode($data, true);            
            $invoiceAddresses = [
                'billing_address' => $data['billAddr'],
                'delivery_address' => $data['deliAddr'],
            ];
        }
        
        $billingAddress = "";
        $deliveryAddress = "";        
        if(str_starts_with($invoiceAddresses['billing_address'], $customerAddress->address_lines)) {
            $billingAddress = $customerAddress;            
        }
        else {                        
            $addresses = Address::select('address_lines','district','state','pincode')->where('customer_id',$customerId)->get();                                    
            foreach($addresses as $address) {                                
                if(str_starts_with($invoiceAddresses['billing_address'], $address->address_lines)) {
                    $billingAddress = $address;
                }
            }
        }        
        if(str_starts_with($invoiceAddresses['delivery_address'], $customerAddress->address_lines)) {
            $deliveryAddress = $customerAddress;
        }
        else {
            $addresses = Address::select('address_lines','district','state','pincode')->where('customer_id',$customerId)->get();
            foreach($addresses as $address) {
                if(str_starts_with($invoiceAddresses['delivery_address'], $address->address_lines)) {
                    $deliveryAddress = $address;
                }
            }
        }

        return [
            'billing' => $billingAddress,
            'delivery' => $deliveryAddress
        ];       
    }
}
