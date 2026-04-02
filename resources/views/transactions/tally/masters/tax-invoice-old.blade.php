<ENVELOPE>
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
                    <VOUCHER REMOTEID="{{ $invoice->remote_id }}" VCHTYPE="Gst Sales" ACTION="Create">
                        <ADDRESS.LIST TYPE="String">
                            @foreach(explode("\n", $invoice->address_data['billing']['address_lines']) as $line)
                                <ADDRESS>{{ trim($line) }}</ADDRESS>
                            @endforeach
                        </ADDRESS.LIST>                        
                        <DISPATCHFROMADDRESS.LIST TYPE="String">
                            <DISPATCHFROMADDRESS>14-A, Vaiyapurigoundanoor,</DISPATCHFROMADDRESS>
                            <DISPATCHFROMADDRESS>Uppidamangalam (Po),</DISPATCHFROMADDRESS>
                            <DISPATCHFROMADDRESS>Karur - 639114</DISPATCHFROMADDRESS>
                            <DISPATCHFROMADDRESS>Cell.No: 9842089525</DISPATCHFROMADDRESS>
                        </DISPATCHFROMADDRESS.LIST>
                        <BASICBUYERADDRESS.LIST TYPE="String">
                            @foreach(explode("\n", $invoice->address_data['billing']['address_lines']) as $line)
                                <ADDRESS>{{ trim($line) }}</ADDRESS>
                            @endforeach
                            <ADDRESS>{{ $invoice->address_data['billing']['district'] }}</ADDRESS>
                            <ADDRESS>{{ $invoice->address_data['billing']['state'] . " - " . $invoice->address_data['billing']['pincode'] }}</ADDRESS>
                        </BASICBUYERADDRESS.LIST>
                        <DATE>{{ str_replace('-', '', $invoice->invoice_date) }}</DATE>
                        <GSTREGISTRATIONTYPE>Regular</GSTREGISTRATIONTYPE>
                        <STATENAME>Tamil Nadu</STATENAME>
                        <VOUCHERTYPENAME>Gst Sales</VOUCHERTYPENAME>
                        <PARTYGSTIN>{{ $invoice->customer->gst_number }}</PARTYGSTIN>
                        <PLACEOFSUPPLY>{{ $invoice->address_data['delivery']['state'] }}</PLACEOFSUPPLY>
                        <PARTYNAME>{{ $invoice->customer->customer_name }}</PARTYNAME>
                        <PARTYLEDGERNAME>{{ $invoice->customer->customer_code }}</PARTYLEDGERNAME>
                        <REFERENCE>{{ $invoice->invoice_num }}</REFERENCE>
                        <PARTYMAILINGNAME>{{ $invoice->customer->customer_name }}</PARTYMAILINGNAME>
                        <PARTYPINCODE>{{ $invoice->address_data['delivery']['pincode'] }}</PARTYPINCODE>
                        <BILLTOPLACE>{{ $invoice->address_data['billing']['district'] }}</BILLTOPLACE>
                        <DISPATCHFROMNAME>Aasaii Food Productt</DISPATCHFROMNAME>
                        <DISPATCHFROMSTATENAME>Tamil Nadu</DISPATCHFROMSTATENAME>
                        <DISPATCHFROMPINCODE>620013</DISPATCHFROMPINCODE>
                        <DISPATCHFROMPLACE>Karur</DISPATCHFROMPLACE>
                        <SHIPTOPLACE>{{ $invoice->address_data['delivery']['district'] }}</SHIPTOPLACE>
                        <CONSIGNEEGSTIN>{{ $invoice->customer->gst_number }}</CONSIGNEEGSTIN>
                        <CONSIGNEEMAILINGNAME>{{ $invoice->customer->customer_name }}</CONSIGNEEMAILINGNAME>
                        <CONSIGNEEPINCODE>{{ $invoice->address_data['billing']['pincode'] }}</CONSIGNEEPINCODE>
                        <CONSIGNEESTATENAME>{{ $invoice->address_data['billing']['state'] }}</CONSIGNEESTATENAME>
                        <VOUCHERNUMBER>{{ $invoice->invoice_num }}</VOUCHERNUMBER>
                        <BASICBASEPARTYNAME>{{ $invoice->customer->customer_name }}</BASICBASEPARTYNAME>
                        <PERSISTEDVIEW>Invoice Voucher View</PERSISTEDVIEW>
                        <BASICBUYERNAME>{{ $invoice->customer->customer_name }}</BASICBUYERNAME>
                        <BASICSHIPVESSELNO>{{ $invoice->vehicle_num }}</BASICSHIPVESSELNO>
                        <CONSIGNEECOUNTRYNAME>India</CONSIGNEECOUNTRYNAME>
                        <BUYERPINNUMBER>{{ $invoice->address_data['billing']['pincode'] }}</BUYERPINNUMBER>
                        <CONSIGNEEPINNUMBER>{{ $invoice->address_data['delivery']['pincode'] }}</CONSIGNEEPINNUMBER>
                        <VCHENTRYMODE>Item Invoice</VCHENTRYMODE>
                        <VOUCHERTYPEORIGNAME>Milk Sales</VOUCHERTYPEORIGNAME>
                        <EFFECTIVEDATE>{{ str_replace('-', '', $invoice->invoice_date) }}</EFFECTIVEDATE>
                        <ISINVOICE>Yes</ISINVOICE>
                        <BASICDATETIMEOFINVOICE>{{ getInvoiceDateTimeForTally($invoice->created_at) }}</BASICDATETIMEOFINVOICE>

                        @foreach($invoiceItems as $item)
                            <ALLINVENTORYENTRIES.LIST>
                                <STOCKITEMNAME>{{ $item->item_code }}</STOCKITEMNAME>
                                <HSNCODE>{{ $item->hsn_code }}</HSNCODE>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <RATE>{{ $item->amount / $item->qty }}/{{ $item->unit }}</RATE>
                                <AMOUNT>{{ $item->amount }}</AMOUNT>
                                <ACTUALQTY>{{ $item->qty }} {{ $item->unit }}</ACTUALQTY>
                                <BILLEDQTY>{{ $item->qty }} {{ $item->unit }}</BILLEDQTY>
                                <ACCOUNTINGALLOCATIONS.LIST>
                                    <LEDGERNAME>{{ $item->item_ledger }}</LEDGERNAME>
                                    <GSTOVRDNTAXABILITY>Taxable</GSTOVRDNTAXABILITY>
                                    <HSNCODE>{{ $item->hsn_code }}</HSNCODE>
                                    <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                    <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                    <AMOUNT>{{ $item->amount }}</AMOUNT>                                    
                                    <RATEDETAILS.LIST>
                                        <GSTRATEDUTYHEAD>Integrated Tax</GSTRATEDUTYHEAD>
                                        <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                    </RATEDETAILS.LIST>
                                    <RATEDETAILS.LIST>
                                        <GSTRATEDUTYHEAD>Central Tax</GSTRATEDUTYHEAD>
                                        <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                    </RATEDETAILS.LIST>
                                    <RATEDETAILS.LIST>
                                        <GSTRATEDUTYHEAD>State Tax</GSTRATEDUTYHEAD>
                                        <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                    </RATEDETAILS.LIST>
                                    <RATEDETAILS.LIST>
                                        <GSTRATEDUTYHEAD>Cess</GSTRATEDUTYHEAD>
                                        <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                    </RATEDETAILS.LIST>
                                </ACCOUNTINGALLOCATIONS.LIST>
                            </ALLINVENTORYENTRIES.LIST>
                        @endforeach

                        <LEDGERENTRIES.LIST>
                            <LEDGERNAME>{{ $invoice->customer->customer_code }}</LEDGERNAME>
                            <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
                            <ISPARTYLEDGER>Yes</ISPARTYLEDGER>
                            <AMOUNT>-{{ $invoice->net_amt }}</AMOUNT>
                        </LEDGERENTRIES.LIST>

                        @if($invoice->has_sgst)
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>OUTPUT CGST</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                <AMOUNT>{{ $invoice->tax_amt / 2 }}</AMOUNT>
                                <VATEXPAMOUNT>{{ $invoice->tax_amt / 2 }}</VATEXPAMOUNT>
                            </LEDGERENTRIES.LIST>
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>OUTPUT SGST</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                <AMOUNT>{{ $invoice->tax_amt / 2 }}</AMOUNT>
                                <VATEXPAMOUNT>{{ $invoice->tax_amt / 2 }}</VATEXPAMOUNT>
                            </LEDGERENTRIES.LIST>
                        @else
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>OUTPUT IGST</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                <AMOUNT>{{ $invoice->tax_amt }}</AMOUNT>
                                <VATEXPAMOUNT>{{ $invoice->tax_amt }}</VATEXPAMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif

                        @if(!empty($invoice->tcs))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>TCS COLLECTION</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                <AMOUNT>{{ $invoice->tcs }}</AMOUNT>
                                <VATEXPAMOUNT>{{ $invoice->tcs }}</VATEXPAMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif

                        @if(!empty($invoice->discount))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>Sales Discount</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                <AMOUNT>-{{ $invoice->discount }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif

                        @if(!empty($invoice->round_off))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>ROUND OF</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <ISPARTYLEDGER>No</ISPARTYLEDGER>
                                <AMOUNT>{{ $invoice->round_off }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif
                    </VOUCHER>
                </TALLYMESSAGE>
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>