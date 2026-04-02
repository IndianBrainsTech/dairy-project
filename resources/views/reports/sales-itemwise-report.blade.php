@extends('app-layouts.admin-master')

@section('title', 'Item wise Sales Report')

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
                    @slot('title') Item wise Sales Report @endslot
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
                            <form method="post" action="{{ route('report.sales.item-wise') }}">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                <label class="my-text">Report Type</label>
                                <select name="reportType" id="reportType" class="my-control">
                                    <option value="Count" @selected($reportType=="Count")>Qty</option>
                                    <option value="Amount" @selected($reportType=="Amount")>Amount</option>
                                    <option value="Both" @selected($reportType=="Both")>Qty & Amount</option>
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                <button id="btnExport" class="btn btn-pink py-0 px-2"><i class="mdi mdi-file-excel font-18"></i></button>
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
                                            <h3 class="text-center pb-2" style="color:blue">Item wise Sales Report</h3>
                                            <h4 class="text-center pb-3">{{ formatDateRange($fromDate,$toDate) }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <table id="reportTable">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th>S.No</th>
                                            <th>Item</th>
                                            @if($reportType == "Count")
                                                @foreach(['Damage Qty', 'Spoilage Qty', 'Sample Qty', 'Ghee Qty', 'Qty', 'Total Qty'] as $field)
                                                    <th>{{$field}}</th>
                                                @endforeach
                                            @elseif($reportType == "Amount")
                                                @foreach(['Damage Amt', 'Spoilage Amt', 'Sample Amt', 'Ghee Amt', 'Amount', 'Total Amt'] as $field)
                                                    <th>{{$field}}</th>
                                                @endforeach
                                            @else
                                                @foreach(['Damage Qty', 'Damage Amt', 'Spoilage Qty', 'Spoilage Amt', 'Sample Qty', 'Sample Amt', 'Ghee Qty', 'Ghee Amt', 'Qty', 'Amount', 'Total Qty', 'Total Amt'] as $field)
                                                    <th>{{$field}}</th>
                                                @endforeach
                                            @endif
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td class="text-center">{{ $loop->index + 1 }} </td>
                                                <td class="text-nowrap">{{ $record['product'] }}</td>
                                                @if(array_key_exists('damage_qty',$record)) <td class="text-right pr-2">{{ $record['damage_qty'] }}</td> @endif
                                                @if(array_key_exists('damage_amt',$record)) <td class="text-right pr-2">{{ $record['damage_amt'] }}</td> @endif
                                                @if(array_key_exists('spoilage_qty',$record)) <td class="text-right pr-2">{{ $record['spoilage_qty'] }}</td> @endif
                                                @if(array_key_exists('spoilage_amt',$record)) <td class="text-right pr-2">{{ $record['spoilage_amt'] }}</td> @endif
                                                @if(array_key_exists('sample_qty',$record)) <td class="text-right pr-2">{{ $record['sample_qty'] }}</td> @endif
                                                @if(array_key_exists('sample_amt',$record)) <td class="text-right pr-2">{{ $record['sample_amt'] }}</td> @endif
                                                @if(array_key_exists('ghee_qty',$record)) <td class="text-right pr-2">{{ $record['ghee_qty'] }}</td> @endif
                                                @if(array_key_exists('ghee_amt',$record)) <td class="text-right pr-2">{{ $record['ghee_amt'] }}</td> @endif
                                                @if(array_key_exists('regular_qty',$record)) <td class="text-right pr-2">{{ $record['regular_qty'] }}</td> @endif
                                                @if(array_key_exists('regular_amt',$record)) <td class="text-right pr-2">{{ $record['regular_amt'] }}</td> @endif
                                                @if(array_key_exists('total_qty',$record)) <td class="text-right pr-2">{{ $record['total_qty'] }}</td> @endif
                                                @if(array_key_exists('total_amt',$record)) <td class="text-right pr-2">{{ $record['total_amt'] }}</td> @endif
                                                <td class="text-center">{{ $record['type'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr class="text-right pr-2">
                                            <th colspan="2" class="text-center">Grand Total</th>
                                            @if(array_key_exists('damage_qty',$record)) <th>{{ $totals['damage_qty'] }}</th> @endif
                                            @if(array_key_exists('damage_amt',$record)) <th>{{ $totals['damage_amt'] }}</th> @endif
                                            @if(array_key_exists('spoilage_qty',$record)) <th>{{ $totals['spoilage_qty'] }}</th> @endif
                                            @if(array_key_exists('spoilage_amt',$record)) <th>{{ $totals['spoilage_amt'] }}</th> @endif
                                            @if(array_key_exists('sample_qty',$record)) <th>{{ $totals['sample_qty'] }}</th> @endif
                                            @if(array_key_exists('sample_amt',$record)) <th>{{ $totals['sample_amt'] }}</th> @endif
                                            @if(array_key_exists('ghee_qty',$record)) <th>{{ $totals['ghee_qty'] }}</th> @endif
                                            @if(array_key_exists('ghee_amt',$record)) <th>{{ $totals['ghee_amt'] }}</th> @endif
                                            @if(array_key_exists('regular_qty',$record)) <th>{{ $totals['regular_qty'] }}</th> @endif
                                            @if(array_key_exists('regular_amt',$record)) <th>{{ $totals['regular_amt'] }}</th> @endif
                                            @if(array_key_exists('total_qty',$record)) <th>{{ $totals['total_qty'] }}</th> @endif
                                            @if(array_key_exists('total_amt',$record)) <th>{{ $totals['total_amt'] }}</th> @endif
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>

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
                    var url = "{{ route('export.sales.item-wise') }}?" + $.param(query);
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