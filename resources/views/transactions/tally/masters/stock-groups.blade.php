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
                    <TALLYMESSAGE xmlns:UDF="TallyUDF">
                        <STOCKGROUP NAME="{{ $group->name }}">
                            <BASEUNITS>{{ $group->unit }}</BASEUNITS>
                            <GSTDETAILS.LIST>
                                <APPLICABLEFROM>20240401</APPLICABLEFROM>
                                <HSNCODE>{{ $group->hsn_code }}</HSNCODE>
                                <TAXABILITY>{{ $group->taxability }}</TAXABILITY>
                                @if($group->taxability == "Taxable")
                                    <STATEWISEDETAILS.LIST>
                                        <STATENAME>Any</STATENAME>
                                        <RATEDETAILS.LIST>
                                            <GSTRATEDUTYHEAD>Central Tax</GSTRATEDUTYHEAD>
                                            <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                            <GSTRATE>{{ $group->cgst }}</GSTRATE>
                                        </RATEDETAILS.LIST>
                                        <RATEDETAILS.LIST>
                                            <GSTRATEDUTYHEAD>State Tax</GSTRATEDUTYHEAD>
                                            <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                            <GSTRATE>{{ $group->sgst }}</GSTRATE>
                                        </RATEDETAILS.LIST>
                                        <RATEDETAILS.LIST>
                                            <GSTRATEDUTYHEAD>Integrated Tax</GSTRATEDUTYHEAD>
                                            <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                            <GSTRATE>{{ $group->igst }}</GSTRATE>
                                        </RATEDETAILS.LIST>
                                        <RATEDETAILS.LIST>
                                            <GSTRATEDUTYHEAD>Cess</GSTRATEDUTYHEAD>
                                            <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                        </RATEDETAILS.LIST>
                                        <RATEDETAILS.LIST>
                                            <GSTRATEDUTYHEAD>Cess on Qty</GSTRATEDUTYHEAD>
                                            <GSTRATEVALUATIONTYPE>Based on Quantity</GSTRATEVALUATIONTYPE>
                                        </RATEDETAILS.LIST>                                    
                                    </STATEWISEDETAILS.LIST>
                                @endif
                            </GSTDETAILS.LIST>
                            <LANGUAGENAME.LIST>
                                <NAME.LIST TYPE="String">
                                    <NAME>{{ $group->name }}</NAME>
                                </NAME.LIST>
                            </LANGUAGENAME.LIST>
                        </STOCKGROUP>
                    </TALLYMESSAGE>
                @endforeach
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>