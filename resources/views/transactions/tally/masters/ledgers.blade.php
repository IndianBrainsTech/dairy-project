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
                    <TALLYMESSAGE>
                        <LEDGER NAME="{{ $ledger->customer_name }}">
                            <PARENT>{{ $ledger->route->name }}</PARENT>
                            <MAILINGNAME.LIST TYPE="String">
                                <MAILINGNAME>{{ $ledger->customer_name }}</MAILINGNAME>
                            </MAILINGNAME.LIST>
                            <ADDRESS.LIST TYPE="String">
                                @foreach(explode("\n", $ledger->address_lines) as $line)
                                    <ADDRESS>{{ trim($line) }}</ADDRESS>
                                @endforeach                                
                            </ADDRESS.LIST>
                            <PINCODE>{{ $ledger->pincode }}</PINCODE>
                            <LEDGERPHONE>{{ $ledger->contact_num }}</LEDGERPHONE>
                            <LEDSTATENAME>{{ $ledger->state }}</LEDSTATENAME>
                            <COUNTRYNAME>India</COUNTRYNAME>
                            <OPENINGBALANCE></OPENINGBALANCE>
                            <INCOMETAXNUMBER>{{ $ledger->pan_number }}</INCOMETAXNUMBER>
                            <GSTREGISTRATIONTYPE>{{ $ledger->gst_registration_type }}</GSTREGISTRATIONTYPE>
                            <PARTYGSTIN>{{ $ledger->gst_number }}</PARTYGSTIN>
                            <LANGUAGENAME.LIST>
                                <NAME.LIST TYPE="String">
                                    <NAME>{{ $ledger->customer_name }}</NAME>
                                    <NAME>{{ $ledger->customer_code }}</NAME>
                                </NAME.LIST>
                            </LANGUAGENAME.LIST>
                        </LEDGER>
                    </TALLYMESSAGE>
                @endforeach
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>