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
                    <VOUCHER REMOTEID="{{ $voucher->remote_id }}" VCHTYPE="Gst Sales" ACTION="Create" OBJVIEW="Invoice Voucher View">
                        <ISINVOICE>Yes</ISINVOICE>
                        <DATE>{{ $voucher->date }}</DATE>
                        <EFFECTIVEDATE>{{ $voucher->date }}</EFFECTIVEDATE>
                        <REFERENCEDATE>{{ $voucher->date }}</REFERENCEDATE>
                        <BASICDATETIMEOFINVOICE>{{ $voucher->date_time }}</BASICDATETIMEOFINVOICE>

                        <VOUCHERNUMBER>{{ $voucher->invoice_number }}</VOUCHERNUMBER>
                        <REFERENCE>{{ $voucher->invoice_number }}</REFERENCE>
                        <VOUCHERTYPENAME>Gst Sales</VOUCHERTYPENAME>
                        <VOUCHERTYPEORIGNAME>Gst Sales</VOUCHERTYPEORIGNAME>
                        <VCHENTRYMODE>Item Invoice</VCHENTRYMODE>
                        <NUMBERINGSTYLE>Automatic (Manual Override)</NUMBERINGSTYLE>
                        <PERSISTEDVIEW>Invoice Voucher View</PERSISTEDVIEW>
                        <ENTEREDBY>{{ $voucher->entered_by }}</ENTEREDBY>
                        <CMPGSTIN>33AANFA9261A1ZP</CMPGSTIN>
                        <CMPGSTSTATE>Tamil Nadu</CMPGSTSTATE>                        

                        <GSTREGISTRATIONTYPE>{{ $voucher->registration_type }}</GSTREGISTRATIONTYPE>
                        <ISELIGIBLEFORITC>Yes</ISELIGIBLEFORITC>
                        <VCHGSTSTATUSISINCLUDED>Yes</VCHGSTSTATUSISINCLUDED>
                        <VCHGSTSTATUSISAPPLICABLE>Yes</VCHGSTSTATUSISAPPLICABLE>

                        <PARTYNAME>{{ $voucher->customer['name'] }}</PARTYNAME>
                        <PARTYLEDGERNAME>{{ $voucher->customer['code'] }}</PARTYLEDGERNAME>
                        <BASICBASEPARTYNAME>{{ $voucher->customer['name'] }}</BASICBASEPARTYNAME>
                        <PARTYMAILINGNAME>{{ $voucher->customer['name'] }}</PARTYMAILINGNAME>
                        <PARTYGSTIN>{{ $voucher->customer['gst_number'] }}</PARTYGSTIN>
                        <PARTYPINCODE>{{ $voucher->billing['pincode'] }}</PARTYPINCODE>
                        <STATENAME>{{ $voucher->billing['state'] }}</STATENAME>
                        <COUNTRYOFRESIDENCE>India</COUNTRYOFRESIDENCE>
                        <BILLTOPLACE>{{ $voucher->billing['district'] }}</BILLTOPLACE>
                        <ADDRESS.LIST>
                            @foreach($voucher->billing['address_lines'] as $line)
                                <ADDRESS>{{ trim($line) }}</ADDRESS>
                            @endforeach
                        </ADDRESS.LIST>

                        <BASICBUYERNAME>{{ $voucher->customer['name'] }}</BASICBUYERNAME>
                        <BUYERPINNUMBER>{{ $voucher->billing['pincode'] }}</BUYERPINNUMBER>
                        <BASICBUYERADDRESS.LIST>
                            @foreach($voucher->billing['address_lines'] as $line)
                                <BASICBUYERADDRESS>{{ trim($line) }}</BASICBUYERADDRESS>
                            @endforeach                            
                        </BASICBUYERADDRESS.LIST>

                        <CONSIGNEEMAILINGNAME>{{ $voucher->customer['name'] }}</CONSIGNEEMAILINGNAME>
                        <CONSIGNEEGSTIN>{{ $voucher->customer['gst_number'] }}</CONSIGNEEGSTIN>
                        <CONSIGNEEPINCODE>{{ $voucher->delivery['pincode'] }}</CONSIGNEEPINCODE>
                        <CONSIGNEESTATENAME>{{ $voucher->delivery['state'] }}</CONSIGNEESTATENAME>
                        <CONSIGNEECOUNTRYNAME>India</CONSIGNEECOUNTRYNAME>
                        <PLACEOFSUPPLY>{{ $voucher->delivery['state'] }}</PLACEOFSUPPLY>
                        <SHIPTOPLACE>{{ $voucher->delivery['district'] }}</SHIPTOPLACE>
                        <BASICCONSIGNEEADDRESS.LIST>
                            @foreach($voucher->delivery['address_lines'] as $line)
                                <BASICCONSIGNEEADDRESS>{{ trim($line) }}</BASICCONSIGNEEADDRESS>
                            @endforeach                            
                        </BASICCONSIGNEEADDRESS.LIST>

                        <DISPATCHFROMNAME>Aasaii Food Productt</DISPATCHFROMNAME>
                        <DISPATCHFROMPLACE>Karur</DISPATCHFROMPLACE>
                        <DISPATCHFROMPINCODE>639114</DISPATCHFROMPINCODE>
                        <DISPATCHFROMSTATENAME>Tamil Nadu</DISPATCHFROMSTATENAME>
                        <DISPATCHFROMADDRESS.LIST>
                            <DISPATCHFROMADDRESS>14-A, Vaiyapurigoundanoor,</DISPATCHFROMADDRESS>
                            <DISPATCHFROMADDRESS>Uppidamangalam (Po),</DISPATCHFROMADDRESS>
                            <DISPATCHFROMADDRESS>Karur - 639114</DISPATCHFROMADDRESS>
                            <DISPATCHFROMADDRESS>Cell No: 9842089525</DISPATCHFROMADDRESS>
                        </DISPATCHFROMADDRESS.LIST>
                        <BASICSHIPVESSELNO>{{ $voucher->vehicle_number }}</BASICSHIPVESSELNO>
                        
                        @foreach($items as $item)
                            <ALLINVENTORYENTRIES.LIST>
                                <STOCKITEMNAME>{{ $item->code }}</STOCKITEMNAME>
                                <GSTHSNNAME>{{ $item->hsn_code }}</GSTHSNNAME>
                                <GSTOVRDNTAXABILITY>Taxable</GSTOVRDNTAXABILITY>
                                <GSTOVRDNINELIGIBLEITC>Applicable</GSTOVRDNINELIGIBLEITC>
                                <GSTOVRDNTYPEOFSUPPLY>Goods</GSTOVRDNTYPEOFSUPPLY>
                                <GSTOVRDNSTOREDNATURE>{{ $voucher->gst_nature }}</GSTOVRDNSTOREDNATURE>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <RATE>{{ $item->rate }}</RATE>
                                <AMOUNT>{{ $item->amount }}</AMOUNT>
                                <ACTUALQTY>{{ $item->qty }} {{ $item->unit }}</ACTUALQTY>
                                <BILLEDQTY>{{ $item->qty }} {{ $item->unit }}</BILLEDQTY>
                                <ACCOUNTINGALLOCATIONS.LIST>
                                    <LEDGERNAME>{{ $item->ledger }}</LEDGERNAME>
                                    <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                    <AMOUNT>{{ $item->amount }}</AMOUNT>                                    
                                </ACCOUNTINGALLOCATIONS.LIST>
                            </ALLINVENTORYENTRIES.LIST>
                        @endforeach

                        <LEDGERENTRIES.LIST>
                            <LEDGERNAME>{{ $voucher->customer['code'] }}</LEDGERNAME>
                            <ISPARTYLEDGER>Yes</ISPARTYLEDGER>
                            <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>                            
                            <AMOUNT>-{{ $voucher->net_amt }}</AMOUNT>
                        </LEDGERENTRIES.LIST>

                        @if($voucher->is_igst)
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>OUTPUT IGST</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>                                
                                <AMOUNT>{{ $voucher->tax_amt }}</AMOUNT>                                
                            </LEDGERENTRIES.LIST>                                                        
                        @else
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>OUTPUT SGST</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>                                
                                <AMOUNT>{{ $voucher->tax_amt / 2 }}</AMOUNT>                                
                            </LEDGERENTRIES.LIST>
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>OUTPUT CGST</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>                                
                                <AMOUNT>{{ $voucher->tax_amt / 2 }}</AMOUNT>                                
                            </LEDGERENTRIES.LIST>
                        @endif

                        @if(!empty($voucher->tcs))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>TCS COLLECTION</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>                                
                                <AMOUNT>{{ $voucher->tcs }}</AMOUNT>                                
                            </LEDGERENTRIES.LIST>
                        @endif

                        @if(!empty($voucher->discount))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>Sales Discount</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>                                
                                <AMOUNT>-{{ $voucher->discount }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif

                        @if(!empty($voucher->round_off))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>ROUND OF</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>                                
                                <AMOUNT>{{ $voucher->round_off }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif
                    </VOUCHER>
                </TALLYMESSAGE>
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>