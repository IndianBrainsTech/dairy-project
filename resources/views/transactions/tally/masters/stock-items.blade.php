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
                @php($types = ["","DAMAGE","SPOILAGE","SAMPLE"])
                @foreach($items as $item)
                    @foreach($types as $type)
                        @php($name = $type ? ($type . " " . $item->name) : $item->name)
                        @php($code = $type ? ($type . " " . $item->item_code) : $item->item_code)
                        <TALLYMESSAGE xmlns:UDF="TallyUDF">                            
                            <STOCKITEM NAME="{{ $name }}">
                                <PARENT>{{ $item->prod_group->name }}</PARENT>
                                <GSTAPPLICABLE>Applicable</GSTAPPLICABLE>
                                <GSTTYPEOFSUPPLY>Goods</GSTTYPEOFSUPPLY>
                                <BASEUNITS>{{ $item->base_unit }}</BASEUNITS>
                                @foreach(json_decode($item->addt_units) as $unit)
                                    <ADDITIONALUNITS>
                                        <NUMERATOR>{{ $unit->conversion }}</NUMERATOR>
                                        <DENOMINATOR>1</DENOMINATOR>
                                        <UNIT>{{ $unit->unit_name }}</UNIT>
                                    </ADDITIONALUNITS>
                                @endforeach
                                <GSTDETAILS.LIST>
                                    <APPLICABLEFROM>20240401</APPLICABLEFROM>
                                    <CALCULATIONTYPE>On Value</CALCULATIONTYPE>
                                    <HSNCODE>{{ $item->hsn_code }}</HSNCODE>
                                    <TAXABILITY>{{ $item->taxability }}</TAXABILITY>
                                    @if($item->taxability == "Taxable")
                                        <STATEWISEDETAILS.LIST>
                                            <STATENAME>Any</STATENAME>
                                            <RATEDETAILS.LIST>
                                                <GSTRATEDUTYHEAD>Central Tax</GSTRATEDUTYHEAD>
                                                <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                                <GSTRATE>{{ $item->cgst }}</GSTRATE>
                                            </RATEDETAILS.LIST>
                                            <RATEDETAILS.LIST>
                                                <GSTRATEDUTYHEAD>State Tax</GSTRATEDUTYHEAD>
                                                <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                                <GSTRATE>{{ $item->sgst }}</GSTRATE>
                                            </RATEDETAILS.LIST>
                                            <RATEDETAILS.LIST>
                                                <GSTRATEDUTYHEAD>Integrated Tax</GSTRATEDUTYHEAD>
                                                <GSTRATEVALUATIONTYPE>Based on Value</GSTRATEVALUATIONTYPE>
                                                <GSTRATE>{{ $item->igst }}</GSTRATE>
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
                                        <NAME>{{ $name }}</NAME>
                                        <NAME>{{ $code }}</NAME>
                                    </NAME.LIST>
                                </LANGUAGENAME.LIST>
                            </STOCKITEM>
                        </TALLYMESSAGE>
                    @endforeach
                @endforeach
            </REQUESTDATA>
        </IMPORTDATA>
    </BODY>
</ENVELOPE>