<ENVELOPE>
    <HEADER>
        <TALLYREQUEST>Import Data</TALLYREQUEST>
    </HEADER>
    <BODY>
        <IMPORTDATA>
            <REQUESTDESC>
                <REPORTNAME>All Masters</REPORTNAME>
            </REQUESTDESC>
            <REQUESTDATA>
                @foreach($ledgers as $ledger)
                    <TALLYMESSAGE xmlns:UDF="TallyUDF">
                        <LEDGER>
                            <NAME>{{ $ledger->name }}</NAME>
                            <PARENT>{{ $ledger->parent }}</PARENT>
                            <OPENINGBALANCE>{{ $ledger->opening_balance }}</OPENINGBALANCE>
                            <ISBILLWISEON>{{ $ledger->is_billwise_on ? 'Yes' : 'No' }}</ISBILLWISEON>
                            <TAXCLASSIFICATIONNAME>{{ $ledger->tax_classification }}</TAXCLASSIFICATIONNAME>
                            <GSTAPPLICABLE>{{ $ledger->gst_applicable }}</GSTAPPLICABLE>
                            <GSTTYPE>{{ $ledger->gst_type }}</GSTTYPE>
                            <TAXTYPE>{{ $ledger->tax_type }}</TAXTYPE>
                            <LEDSTATENAME>{{ $ledger->state }}</LEDSTATENAME>
                            <COUNTRYNAME>{{ $ledger->country }}</COUNTRYNAME>
                            <GSTIN>{{ $ledger->gstin }}</GSTIN>
                        </LEDGER>
                    </TALLYMESSAGE>
                @endforeach
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>