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
                @foreach($units as $unit)
                    <TALLYMESSAGE>
                        <UNIT NAME="{{ $unit->display_name }}">
                            <NAME>{{ $unit->display_name }}</NAME>
                            @if($unit->display_name != $unit->unit_name)
                                <ORIGINALNAME>{{ $unit->unit_name }}</ORIGINALNAME>
                            @endif
                            <ISSIMPLEUNIT>Yes</ISSIMPLEUNIT>
                            <DECIMALPLACES>2</DECIMALPLACES>
                        </UNIT>
                    </TALLYMESSAGE>
                @endforeach
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>