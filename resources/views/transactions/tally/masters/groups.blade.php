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
                @foreach($groups as $group)
                    <TALLYMESSAGE>
                        <GROUP NAME="{{ $group->name }}">
                            <PARENT>Sundry Debtors</PARENT>
                            <LANGUAGENAME.LIST>
                                <NAME.LIST TYPE="String">
                                    <NAME>{{ $group->name }}</NAME>
                                </NAME.LIST>
                            </LANGUAGENAME.LIST>
                        </GROUP>
                    </TALLYMESSAGE>
                @endforeach
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>