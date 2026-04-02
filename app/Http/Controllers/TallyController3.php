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
use App\Models\Orders\SalesInvoiceItem;
use App\Models\Orders\TaxInvoiceItem;
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

    public function tallyInvoices(Request $request)
    {
        $date = $request->input('invoice_date', date('Y-m-d'));        
        
        $salesInvoices = SalesInvoice::select('id','invoice_date','route_name','customer_name','invoice_num')
            ->where('invoice_date',$date)
            ->get();
        
        $taxInvoices = TaxInvoice::select('id','invoice_date','route_name','customer_name','invoice_num')
            ->where('invoice_date',$date)
            ->get();

        // Merge both collections
        $invoices = $salesInvoices->merge($taxInvoices);

        // Sort by customer_name
        $sortedInvoices = $invoices->sortBy('customer_name')->values(); // Reset array keys after sorting

        // return response()->json([
        return view('transactions.tally.tally-invoices', [
            'date'     => $date,            
            'invoices' => $sortedInvoices,
        ]);
    }

    public function tallyInvoice(Request $request)
    {
        $invoiceNum = $request->invoice_num;

        $settings = Setting::where('category', 'Invoice')->pluck('key', 'value'); 
        $invoiceType = $settings->first(function ($key, $value) use ($invoiceNum) {
            return Str::startsWith($invoiceNum, $value) ? $key : null;
        });

        if($invoiceType == "sales-invoice") {
            $invoice = SalesInvoice::select('id','invoice_date','invoice_num','customer_id','amount','tcs','discount','round_off','net_amt','created_at','order_dt')
                ->with('customer:id,customer_name,customer_code')
                ->where('invoice_num',$invoiceNum)
                ->first();
            $invoice->remote_id = $invoice->invoice_num . "-" . preg_replace('/\D/', '', $invoice->order_dt);

            $invoiceItems = SalesInvoiceItem::select('id','product_id','product_name','item_category','hsn_code','qty','amount')
                ->where('invoice_num',$invoiceNum)
                ->get();

            foreach($invoiceItems as &$item) {
                $item->item_ledger = "Milk Sales";
                $unitId = ProductUnit::where('product_id',$item->product_id)->where('prim_unit',1)->value('unit_id');
                $item->unit = UOM::where('id',$unitId)->value('display_name');
                $item->item_code = Product::where('id',$item->product_id)->value('item_code');                
            }

            $viewPath = 'transactions.tally.masters.exempted-invoice';
        }
        else if($invoiceType == "tax-invoice") {
            $invoice = TaxInvoice::select('id','invoice_date','invoice_num','customer_id','tot_amt','tax_amt','tcs','discount','round_off','net_amt','created_at','order_num','order_dt','vehicle_num')
                ->with('customer:id,customer_name,customer_code,gst_number')
                ->where('invoice_num',$invoiceNum)
                ->first();
            $invoice->remote_id = $invoice->invoice_num . "-" . preg_replace('/\D/', '', $invoice->order_dt);
            
            $invoiceItems = TaxInvoiceItem::select('id','product_id','product_name','item_category','hsn_code','qty','amount','gst','sgst')
                ->where('invoice_num',$invoiceNum)
                ->get();

            foreach($invoiceItems as &$item) {
                if($item->gst == 5)
                    $item->item_ledger = "Sales @5%";
                else if($item->gst == 12)
                    $item->item_ledger = "Sales 12%";
                else if($item->gst == 18)
                    $item->item_ledger = "Sales 18%";
                
                $unitId = ProductUnit::where('product_id',$item->product_id)->where('prim_unit',1)->value('unit_id');
                $item->unit = UOM::where('id',$unitId)->value('display_name');
                $item->item_code = Product::where('id',$item->product_id)->value('item_code');                
            }

            $invoice->has_sgst = !empty($invoiceItems[0]->sgst);
            $invoice->address_data = $this->getAddressData($invoice->customer_id, $invoice->order_num);

            $viewPath = 'transactions.tally.masters.tax-invoice';
        }

        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;        
        $xmlContent = $xmlDeclaration . trim(view($viewPath, compact('invoice','invoiceItems'))->render());        

        // Clears any whitespace in the buffer
        if (ob_get_level() > 0) ob_end_clean();
        
        return response($xmlContent)->header('Content-Type', 'application/xml');
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

    public function getAddress() {
        return $this->getAddressData(37, '2324O-10797');
    }

    private function getAddressData($customerId, $orderNum) {
        $customerAddress = Customer::select('address_lines','district','state','pincode')->where('id',$customerId)->first();
        $addressData = Order::where('order_num', $orderNum)->value('address_data');        
        $invoiceAddresses = json_decode($addressData, true)[0];                
        $billingAddress = "";
        $deliveryAddress = "";        
        if(str_starts_with($invoiceAddresses['billing_address'], $customerAddress->address_lines)) {
            $billingAddress = $customerAddress;
        }
        else {            
            $addresses = Address::select('address_lines','district','state','pincode')->where('customer_id',$customerId)->get();                        
            dd($addresses);
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
