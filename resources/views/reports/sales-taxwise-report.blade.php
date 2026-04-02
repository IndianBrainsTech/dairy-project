@extends('app-layouts.admin-master')

@section('title', 'Tax wise Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Tax wise Report @endslot
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
                            <form method="post" action="{{ route('report.sales.tax-wise') }}">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                <label class="my-text">Report Type</label>
                                <select name="reportType" id="reportType" class="my-control">
                                    <option value="Format1" @selected($reportType=="Format1")>Format 1</option>
                                    <option value="Format2" @selected($reportType=="Format2")>Format 2</option>
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                <button id="btnExport" class="btn btn-pink py-0 px-2 mr-2"><i class="mdi mdi-file-excel font-18"></i></button>
                            </form>
                        </div>
                        <hr/>

                        @if(!$records)
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
                                            <h3 class="text-center pb-2" style="color:blue">Tax wise Report</h3>
                                            <h4 class="text-center pb-3">{{ formatDateRange($fromDate,$toDate) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($reportType == "Format1")
                                    <table id="reportTable" class="text-nowrap">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="text-center">HSN Name</th>
                                                <th class="text-center">HSN Code</th>
                                                <th class="text-center">Tax Rate</th>
                                                <th class="text-right pr-2">Taxable Amount</th>
                                                <th class="text-right pr-2">SGST</th>
                                                <th class="text-right pr-2">CGST</th>
                                                <th class="text-right pr-2">IGST</th>
                                                <th class="text-right pr-2">Tax Amount</th>
                                                <th class="text-right pr-2">Taxable Total</th>
                                                <th class="text-right pr-2">Discount</th>
                                                <th class="text-right pr-2">Net Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $record)
                                                <tr>
                                                    <td class="text-center">{{ $record['sno'] }} </td>
                                                    <td class="text-center">{{ $record['hsn_name'] }}</td>
                                                    <td class="text-center">{{ $record['hsn_code'] }}</td>
                                                    <td class="text-center">{{ $record['tax_rate'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['taxable_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['sgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['cgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['igst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['tax_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['gross_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['discount'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['net_amt'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr class="text-right pr-2">
                                                <th colspan="4" class="text-center">Grand Total</th>
                                                <th>{{ $totals['taxable_amt'] }}</th>
                                                <th>{{ $totals['sgst'] }}</th>
                                                <th>{{ $totals['cgst'] }}</th>
                                                <th>{{ $totals['igst'] }}</th>
                                                <th>{{ $totals['tax_amt'] }}</th>
                                                <th>{{ $totals['gross_amt'] }}</th>
                                                <th>{{ $totals['discount'] }}</th>
                                                <th>{{ $totals['net_amt'] }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @elseif($reportType == "Format2")
                                    <table id="reportTable" class="text-nowrap">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="text-center">Tax Rate</th>
                                                <th class="text-right pr-2">Taxable Amount</th>
                                                <th class="text-right pr-2">SGST</th>
                                                <th class="text-right pr-2">CGST</th>
                                                <th class="text-right pr-2">IGST</th>
                                                <th class="text-right pr-2">Tax Amount</th>
                                                <th class="text-right pr-2">Taxable Total</th>
                                                <th class="text-right pr-2">Discount</th>
                                                <th class="text-right pr-2">Net Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $record)
                                                <tr>
                                                    <td class="text-center">{{ $record['sno'] }} </td>
                                                    <td class="text-center">{{ $record['tax_rate'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['taxable_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['sgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['cgst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['igst'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['tax_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['gross_amt'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['discount'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['net_amt'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr class="text-right pr-2">
                                                <th colspan="2" class="text-center">Grand Total</th>
                                                <th>{{ $totals['taxable_amt'] }}</th>
                                                <th>{{ $totals['sgst'] }}</th>
                                                <th>{{ $totals['cgst'] }}</th>
                                                <th>{{ $totals['igst'] }}</th>
                                                <th>{{ $totals['tax_amt'] }}</th>
                                                <th>{{ $totals['gross_amt'] }}</th>
                                                <th>{{ $totals['discount'] }}</th>
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

            $('#fromDate').change(function() {
                var date = $(this).val();
                $('#toDate').attr('min',date);
            });

            $('#btnPrint').on("click", function () {
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#btnExport').click(function(event) {
                event.preventDefault();
                const count = "{{ count($records) }}";
                if(count == 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    var query = {
                        fromDate: $("#fromDate").val(),
                        toDate: $("#toDate").val(),
                        type: $("#reportType").val()
                    };
                    var url = "{{ route('export.sales.tax-wise') }}?" + $.param(query);
                    window.location = url;
                }
            });

            $("#fromDate").trigger('change');
        });  
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop