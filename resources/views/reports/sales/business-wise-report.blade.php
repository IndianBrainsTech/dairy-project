@extends('app-layouts.admin-master')

@section('title', 'Business wise Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        @media print {
            @page {
                size: A4;
                margin: 50px 50px;
            }      
            #reportTable th {
                font-size: 16pt !important;
            }
            #reportTable td {
                font-size: 15pt !important;
            }   
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Business wise Report @endslot
                    @slot('item1') Reports @endslot
                    @slot('item2') Sales Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <label class="my-text">From</label>
                            <input type="date" id="from-date" value="{{ $dates['from'] }}" class="my-control">
                            <label class="my-text">To</label>
                            <input type="date" id="to-date" value="{{ $dates['to'] }}" class="my-control">
                            <label class="my-text">Report Type</label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-info py-1">
                                    <input type="radio" name="report_type" value="Itemized" @checked($report_type=="Itemized")>Itemized
                                </label>
                                <label class="btn btn-outline-info py-1">
                                    <input type="radio" name="report_type" value="HSN-based" @checked($report_type=="HSN-based")>HSN-based
                                </label>
                            </div>
                            <label class="my-text">Business Type</label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-info py-1">
                                    <input type="radio" name="business_type" value="B2B" @checked($business_type=="B2B")>B2B
                                </label>
                                <label class="btn btn-outline-info py-1">
                                    <input type="radio" name="business_type" value="B2C" @checked($business_type=="B2C")>B2C
                                </label>
                                <label class="btn btn-outline-info py-1">
                                    <input type="radio" name="business_type" value="Both" @checked($business_type=="Both")>Both
                                </label>
                            </div>
                            <button id="btn-submit" type="button" class="btn btn-primary btn-sm px-3 mx-3" aria-label="Submit" title="Submit">Submit</button>
                            <button id="btn-print" type="button" class="btn btn-pink btn-sm px-2 mr-2" aria-label="Print" title="Print">&nbsp;<i class="fa fa-print"></i>&nbsp;</button>
                            <button id="btn-export" type="button" class="btn btn-pink py-0 px-2" aria-label="Excel" title="Excel"><i class="mdi mdi-file-excel font-18"></i></button>                            
                        </div>
                        <hr/>

                        @if(!$records && empty(array_filter($filters)))
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found!
                            </div>
                        @else
                            <div id="report-div">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="print-header">
                                            <h3 class="text-center pb-2" style="color:maroon">Aasaii Food Productt</h3>
                                            <h3 class="text-center pb-2" style="color:blue">{{ $report_title }}</h3>
                                            <h4 class="text-center pb-3">{{ formatDateRange($dates['from'], $dates['to']) }}</h4>
                                        </div>
                                    </div>
                                </div>

                                @if($report_type === "Itemized")
                                    <table id="reportTable" class="text-nowrap">
                                        <thead class="thead-light">
                                            <tr class="text-center no-print" style="background-color: white">
                                                <td></td>
                                                <td>
                                                    <select name="hsn_code" id="hsn-code" class="my-control">
                                                        <option value="" @selected($filters['hsn_code']==="")></option>
                                                        @foreach($filter_data['hsn_codes'] as $hsn_code)
                                                            <option value="{{ $hsn_code }}" @selected($filters['hsn_code'] === $hsn_code)>{{ $hsn_code }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="d-none"></td>
                                                <td>
                                                    <select name="category" id="category" class="my-control" style="width:96%">
                                                        <option value="" @selected($filters['category']==="")></option>
                                                        @foreach($filter_data['categories'] as $category)
                                                            <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="unit" id="unit" class="my-control">
                                                        <option value="" @selected($filters['unit']==="")></option>
                                                        @foreach($filter_data['units'] as $unit)
                                                            <option value="{{ $unit }}" @selected($filters['unit'] === $unit)>{{ $unit }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td></td>
                                                <td>
                                                    <select name="tax_rate" id="tax-rate" class="my-control">
                                                        <option value="" @selected($filters['tax_rate']==="")></option>
                                                        @foreach($filter_data['tax_rates'] as $tax_rate)
                                                            <option value="{{ $tax_rate }}" @selected($filters['tax_rate']=== $tax_rate)>{{ $tax_rate }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><button id="btn-filter" class="btn btn-primary py-0 px-3"><i class="typcn typcn-filter"></i></button></td>
                                            </tr>
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="text-center">HSN Code</th>
                                                <th class="text-center d-none">Category</th>
                                                <th class="text-center">Item Name</th>
                                                <th class="text-center">Unit</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Tax Rate</th>
                                                <th class="text-right pr-2">Taxable Amt</th>
                                                <th class="text-right pr-2">SGST</th>
                                                <th class="text-right pr-2">CGST</th>
                                                <th class="text-right pr-2">IGST</th>
                                                <th class="text-right pr-2">Tax Amt</th>
                                                <th class="text-right pr-2">Net Amt</th>
                                            </tr>                                            
                                        </thead>
                                        <tbody>
                                            @foreach($records as $record)
                                                <tr>
                                                    <td class="text-center">{{ $record['sno'] }} </td>
                                                    <td class="text-center">{{ $record['hsn_code'] }}</td>
                                                    <td class="text-center d-none">{{ $record['group'] }}</td>
                                                    <td class="text-left pl-2">{{ $record['product'] }}</td>
                                                    <td class="text-center">{{ $record['uqc'] }}</td>
                                                    <td class="text-right p2-2">{{ $record['total_qty'] }}</td>
                                                    <td class="text-center">{{ $record['tax_rate'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['taxable_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['sgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['cgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['igst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['total_tax'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['total_value'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr class="text-right pr-2">
                                                <th colspan="4" class="text-center">Grand Total</th>
                                                <th>{{ $totals['total_qty'] }}</th>
                                                <th></th>
                                                <th>{{ $totals['taxable_amt'] }}</th>
                                                <th>{{ $totals['sgst'] }}</th>
                                                <th>{{ $totals['cgst'] }}</th>
                                                <th>{{ $totals['igst'] }}</th>
                                                <th>{{ $totals['total_tax'] }}</th>
                                                <th>{{ $totals['total_value'] }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @else
                                    <table id="reportTable" class="text-nowrap">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="text-center">HSN Name</th>
                                                <th class="text-center">HSN Code</th>
                                                <th class="text-right pr-2">Total Qty</th>
                                                <th class="text-center">Tax Rate</th>
                                                <th class="text-right pr-2">Taxable Amt</th>
                                                <th class="text-right pr-2">SGST</th>
                                                <th class="text-right pr-2">CGST</th>
                                                <th class="text-right pr-2">IGST</th>
                                                <th class="text-right pr-2">Tax Amt</th>
                                                <th class="text-right pr-2">Net Amt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $record)
                                                <tr>
                                                    <td class="text-center">{{ $record['sno'] }} </td>
                                                    <td class="text-left pl-2">{{ $record['hsn_name'] }}</td>
                                                    <td class="text-center">{{ $record['hsn_code'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['qty'] }}</td>
                                                    <td class="text-center">{{ $record['tax_rate'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['taxable_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['sgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['cgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['igst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['tax_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['net_amt'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr class="text-right pr-2">
                                                <th colspan="3" class="text-center">Grand Total</th>
                                                <th>{{ $totals['qty'] }}</th>
                                                <th></th>
                                                <th>{{ $totals['taxable_amt'] }}</th>
                                                <th>{{ $totals['sgst'] }}</th>
                                                <th>{{ $totals['cgst'] }}</th>
                                                <th>{{ $totals['igst'] }}</th>
                                                <th>{{ $totals['tax_amt'] }}</th>
                                                <th>{{ $totals['net_amt'] }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @endif
                            </div>
                        @endif                        
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            $('#from-date').change(function() {
                var date = $(this).val();
                $('#to-date').attr('min',date);
            });

            $('#btn-submit, #btn-filter').on("click", doSubmit);

            $('#btn-print').on("click", function () {
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
                $("body").toggleClass("enlarge-menu");
            });

            $('#btn-export').on('click', function () {
                const recordCount = {{ count($records) }};

                if (recordCount === 0) {
                    Swal.fire('Sorry', 'No data found to download', 'warning');
                    return;                    
                }

                const fields = getFields();
                const url = "{{ route('exports.business-wise') }}?" + $.param(fields);
                window.location.href = url;
            });

            $("#fromDate").trigger('change');

            function getFields() {
                let fields = {
                    from_date     : $("#from-date").val(),
                    to_date       : $("#to-date").val(),
                    report_type   : $('input[name="report_type"]:checked').val(),
                    business_type : $('input[name="business_type"]:checked').val(),
                };

                @if($report_type === "Itemized")
                    fields.hsn_code = $("#hsn-code").val();
                    fields.category = $("#category").val();
                    fields.unit     = $("#unit").val();
                    fields.tax_rate = $("#tax-rate").val();
                @endif

                return fields;
            }

            function doSubmit() {
                let fields = getFields();

                // Create a form element
                let form = $('<form>', { method: 'POST', action: "{{ route('reports.sales.business-wise') }}" });
 
                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { type: 'hidden', name: '_token', value: csrfToken }));

                // Add the data as hidden inputs
                $.each(fields, (name, value) => {
                    form.append($('<input>', { type: 'hidden', name: name, value: value }));
                });

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit(); 
            }
        });        

        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop