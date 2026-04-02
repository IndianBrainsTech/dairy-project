<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use SimpleXMLElement;

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
}
