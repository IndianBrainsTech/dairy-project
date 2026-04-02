@extends('app-layouts.admin-master')

@section('title', 'Transaction Report')

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
                @component('app-components.breadcrumb-2')
                    @slot('title') Transaction Report @endslot
                    @slot('item1') Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
 
                        <div>
                            <form method="post" action="{{ route('report.transaction') }}">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="from_date" id="from-date" value="{{ $dates['from'] }}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="to_date" id="to-date" value="{{ $dates['to'] }}" class="my-control">
                                <label class="my-text">Type</label>
                                <select name="type" id="type" class="my-control">
                                    <option value="All" @selected($type=="All")>All</option>
                                    <option value="Sales Invoice" @selected($type=="Sales Invoice")>Sales Invoice</option>
                                    <option value="Tax Invoice" @selected($type=="Tax Invoice")>Tax Invoice</option>
                                    <option value="Bulk Milk Invoice" @selected($type=="Bulk Milk Invoice")>Bulk Milk Invoice</option>
                                    <option value="Cash Receipt" @selected($type=="Cash Receipt")>Cash Receipt</option>
                                    <option value="Bank Receipt" @selected($type=="Bank Receipt")>Bank Receipt</option>
                                    <option value="Incentive Receipt" @selected($type=="Incentive Receipt")>Incentive Receipt</option>
                                    <option value="Deposit Receipt" @selected($type=="Deposit Receipt")>Deposit Receipt</option>
                                    <option value="Sales Return" @selected($type=="Sales Return")>Sales Return</option>
                                    <option value="Credit Note" @selected($type=="Credit Note")>Credit Note</option>
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <button id="btn-print" type="button" class="btn btn-pink py-1 mr-2" title="Print Report"><i class="fa fa-print"></i></button>
                                <button id="btn-export" type="button" class="btn btn-pink py-0 px-2" title="Export Report"><i class="mdi mdi-file-excel font-18"></i></button>
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
                                            <h3 class="text-center pb-2" style="color:blue">Transaction Report</h3>
                                            <h4 class="text-center pb-3">{{ formatDateRange($dates['from'], $dates['to']) }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <table id="reportTable" class="text-nowrap">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th>S.No</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Number</th>
                                            <th>Cust Code</th>
                                            <th>Customer Name</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td class="text-center">{{ $loop->index + 1 }} </td>
                                                <td class="text-center">{{ $record['date'] }}</td>
                                                <td class="pl-2">{{ $record['type'] }}</td>
                                                <td class="pl-2">{{ $record['number'] }}</td>
                                                <td class="pl-2">{{ $record['code'] }}</td>
                                                <td class="pl-2">{{ $record['customer'] }}</td>
                                                <td class="text-right pr-2">{{ $record['debit'] }}</td>
                                                <td class="text-right pr-2">{{ $record['credit'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr class="text-right">
                                            <th colspan="6" class="text-right pr-5">Total</th>
                                            <th class="text-right pr-2">{{ $totals['debit'] }}</th>
                                            <th class="text-right pr-2">{{ $totals['credit'] }}</th>
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>
                        @endif
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!--container-->
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

            $('#btn-print').on("click", function () {
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#btn-export').click(function(event) {
                event.preventDefault();
                const count = "{{ count($records) }}";
                if(count == 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    var query = {
                        from_date : $("#from-date").val(),
                        to_date   : $("#to-date").val(),
                        type      : $("#type").val(),
                    };
                    var url = "{{ route('export.transaction') }}?" + $.param(query);
                    window.location = url;
                }
            });

            $("#from-date").trigger('change');
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop