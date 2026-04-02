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
                        <DATE>{{ $invoice->invoice_date }}</DATE>      
                        <VOUCHERTYPENAME>Milk Sales</VOUCHERTYPENAME>
                        <ENTEREDBY>admin</ENTEREDBY>
                        <PARTYLEDGERNAME>{{ $invoice->customer->customer_name }}</PARTYLEDGERNAME>
                        <REFERENCE>{{ $invoice->invoice_num }}</REFERENCE>
                        <VOUCHERNUMBER>{{ $invoice->invoice_num }}</VOUCHERNUMBER>
                        <PERSISTEDVIEW>Accounting Voucher View</PERSISTEDVIEW>
                        <VOUCHERTYPEORIGNAME>Milk Sales</VOUCHERTYPEORIGNAME>
                        <EFFECTIVEDATE>{{ $invoice->invoice_date }}</EFFECTIVEDATE>
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
                    </VOUCHER>
                </TALLYMESSAGE>                    
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>