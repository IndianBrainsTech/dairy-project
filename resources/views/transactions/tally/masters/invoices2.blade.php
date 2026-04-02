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
                    <VOUCHER VCHTYPE="Milk Sales" ACTION="Create">
                        <DATE>{{ str_replace('-', '', $invoice->invoice_date) }}</DATE>
                        <PARTYNAME>{{ $invoice->customer->customer_name }}</PARTYNAME>
                        <VOUCHERTYPENAME>Milk Sales</VOUCHERTYPENAME>
                        <VOUCHERNUMBER>{{ $invoice->invoice_num }}</VOUCHERNUMBER>
                        <REFERENCE>{{ $invoice->invoice_num }}</REFERENCE>
                        <PARTYLEDGERNAME>{{ $invoice->customer->customer_name }}</PARTYLEDGERNAME>
                        <PERSISTEDVIEW>Invoice Voucher View</PERSISTEDVIEW>
                        <BASICBUYERNAME>{{ $invoice->customer->customer_name }}</BASICBUYERNAME>
                        <BASICDATETIMEOFINVOICE>{{ getInvoiceDateTimeForTally($invoice->created_at) }}</BASICDATETIMEOFINVOICE>
                        <ISINVOICE>Yes</ISINVOICE>
                        <LEDGERENTRIES.LIST>
                            <LEDGERNAME>{{ $invoice->customer->customer_code }}</LEDGERNAME>
                            <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
                            <AMOUNT>-{{ $invoice->net_amt }}</AMOUNT>
                        </LEDGERENTRIES.LIST>
                        @if(!empty($invoice->tax_amt))
                            @if($invoice->has_sgst)
                                <LEDGERENTRIES.LIST>
                                    <LEDGERNAME>OUTPUT CGST</LEDGERNAME>
                                    <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                    <AMOUNT>{{ $invoice->tax_amt / 2 }}</AMOUNT>
                                </LEDGERENTRIES.LIST>
                                <LEDGERENTRIES.LIST>
                                    <LEDGERNAME>OUTPUT SGST</LEDGERNAME>
                                    <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                    <AMOUNT>{{ $invoice->tax_amt / 2 }}</AMOUNT>
                                </LEDGERENTRIES.LIST>
                            @else
                                <LEDGERENTRIES.LIST>
                                    <LEDGERNAME>OUTPUT IGST</LEDGERNAME>
                                    <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                    <AMOUNT>{{ $invoice->tax_amt }}</AMOUNT>
                                </LEDGERENTRIES.LIST>
                            @endif
                        @endif
                        @if(!empty($invoice->tcs))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>TCS COLLECTION</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <AMOUNT>{{ $invoice->tcs }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif
                        @if(!empty($invoice->discount))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>Sales Discount</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <AMOUNT>-{{ $invoice->discount }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif
                        @if(!empty($invoice->round_off))
                            <LEDGERENTRIES.LIST>
                                <LEDGERNAME>ROUND OF</LEDGERNAME>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <AMOUNT>{{ $invoice->round_off }}</AMOUNT>
                            </LEDGERENTRIES.LIST>
                        @endif
                        @foreach($invoiceItems as $item)
                            <ALLINVENTORYENTRIES.LIST>
                                <STOCKITEMNAME>{{ $item->item_code }}</STOCKITEMNAME>
                                <HSNCODE>{{ $item->hsn_code }}</HSNCODE>
                                <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                <AMOUNT>{{ $item->amount }}</AMOUNT>
                                <ACTUALQTY>{{ $item->qty }} {{ $item->unit }}</ACTUALQTY>
                                <BILLEDQTY>{{ $item->qty }} {{ $item->unit }}</BILLEDQTY>
                                <ACCOUNTINGALLOCATIONS.LIST>
                                    <LEDGERNAME>{{ $item->item_ledger }}</LEDGERNAME>
                                    <HSNCODE>{{ $item->hsn_code }}</HSNCODE>
                                    <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                                    <AMOUNT>{{ $item->amount }}</AMOUNT>
                                </ACCOUNTINGALLOCATIONS.LIST>
                            </ALLINVENTORYENTRIES.LIST>
                        @endforeach
                    </VOUCHER>
                </TALLYMESSAGE>                    
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>