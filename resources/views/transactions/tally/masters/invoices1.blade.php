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
                    <VOUCHER VCHTYPE="Milk Sales" ACTION="Create" OBJVIEW="Invoice Voucher View">
                        <DATE>{{ str_replace('-', '', $invoice->invoice_date) }}</DATE>      
                        <VOUCHERTYPENAME>Milk Sales</VOUCHERTYPENAME>
                        <ENTEREDBY>App Sync</ENTEREDBY>
                        <PARTYLEDGERNAME>{{ $invoice->customer->customer_name }}</PARTYLEDGERNAME>
                        <REFERENCE>{{ $invoice->invoice_num }}</REFERENCE>
                        <VOUCHERNUMBER>{{ $invoice->invoice_num }}</VOUCHERNUMBER>
                        <PERSISTEDVIEW>Accounting Voucher View</PERSISTEDVIEW>
                        <VOUCHERTYPEORIGNAME>Milk Sales</VOUCHERTYPEORIGNAME>
                        <EFFECTIVEDATE>{{ str_replace('-', '', $invoice->invoice_date) }}</EFFECTIVEDATE>
                        <LEDGERENTRIES.LIST>
                            <LEDGERNAME>{{ $invoice->customer->customer_code }}</LEDGERNAME>
                            <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
                            <ISPARTYLEDGER>Yes</ISPARTYLEDGER>
                            <AMOUNT>-{{ $invoice->net_amt }}</AMOUNT>
                        </LEDGERENTRIES.LIST>
                        <LEDGERENTRIES.LIST>
                            <LEDGERNAME>Sales</LEDGERNAME>
                            <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
                            <ISPARTYLEDGER>No</ISPARTYLEDGER>
                            <AMOUNT>{{ $invoice->net_amt }}</AMOUNT>
                        </LEDGERENTRIES.LIST>
                        
                        <ALLINVENTORYENTRIES.LIST>
                            <STOCKITEMNAME>FCM 1000 ML</STOCKITEMNAME>                            
                            <RATE>71.66/Ltrs</RATE>
                            <AMOUNT>86.00</AMOUNT>
                            <ACTUALQTY> 1.2 Ltrs</ACTUALQTY>
                            <BILLEDQTY> 1.2 Ltrs</BILLEDQTY>

                            <ACCOUNTINGALLOCATIONS.LIST>
                                <LEDGERNAME>Milk Sales</LEDGERNAME>
                                <AMOUNT>86.00</AMOUNT>
                            </ACCOUNTINGALLOCATIONS.LIST>
                        </ALLINVENTORYENTRIES.LIST>

                    </VOUCHER>
                </TALLYMESSAGE>                    
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>