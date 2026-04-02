@extends('app-layouts.admin-master')

@section('title', 'Trip Sheet')

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
                    @slot('title') Trip Sheet @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Sheets @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">                        
                        <form id="frmLoadingSheet" method="get" action="{{ route('sheets.trip-sheet') }}" class="mb-0" >
                        @csrf
                            <div>
                                <label class="my-text">Date <small class="text-danger font-13">*</small></label>                                
                                <input type="date" name="tsDate" value="{{$date}}" class="my-control" required>
                                <label class="my-text">Route <small class="text-danger font-13">*</small></label>
                                <select name="route" id="route" class="my-control" required class="form-control @error('route') is-invalid @enderror">
                                    <option value="0" @selected($routeId=="0")>Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{$route->id}}" @selected($routeId==$route->id)>{{$route->name}}</option>
                                    @endforeach
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm text-light mx-3 px-3" />
                                <a id="btnPrint" href="#" class="btn btn-pink py-1"><i class="fa fa-print"></i></a>                                
                            </div>                             
                        </form>
                        <hr/>
 
                        @if($routeId == 0)
                            No Route Selected
                        @elseif(count($dataRows) == 0)
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found for this Route.
                            </div>
                        @else
                            <div id="trip-sheet">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="print-header">
                                            <p class="comp-name">Aasaii Food Productt</p>
                                            <p class="address">14-A, Vaiyapurigoundanoor, Uppidamangalam P.O., Karur 639 114. Cell No : 9842089525</p>
                                            <hr class="hr1"/>
                                            <p class="title">Route Trip Sheet</p>
                                            <div style="display: flex; justify-content: space-between;" class="mb-3">
                                                <div class="data">Route : <b>{{ $routeName }}</b></div>
                                                <div class="data">Date : <b>{{ displayDate($date) }}</b></div>
                                            </div>
                                        </div>
                                        <table id="reportTable" class="text-nowrap">
                                            <thead class="thead-light">
                                                <tr class="text-center">
                                                    <th>S.No</th>
                                                    <th>Customer</th>
                                                    <th>Invoice No.</th>
                                                    <th>Qty</th>
                                                    <th>Net Amount</th>
                                                    <th>Rec. Cash</th>
                                                    <th>Rec. Cheque</th>
                                                    <th>Balance</th>
                                                    <th>Iss. Crt</th>
                                                    <th>Rec. Crt</th>
                                                    <th>End KM</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dataRows as $data)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->index + 1 }} </td>
                                                        <td>{{ $data->customer_name }}</td>
                                                        <td>{{ $data->invoice_num }}</td>
                                                        <td class="text-right pr-2">{{ getTwoDigitPrecision($data->qty) }}</td>
                                                        <td class="text-right pr-2">{{ getTwoDigitPrecision($data->net_amt) }}</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td class="text-right pr-2">{{ getTwoDigitPrecision($data->crates) }}</td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <th colspan="3" class="text-center">Total</th>
                                                    <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['qty']) }}</th>
                                                    <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['net_amt']) }}</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['crates']) }}</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>

                                        <div class="print-footer" style="margin-top: 100px; color:black;">
                                            <p style="display: flex; justify-content: space-between; text-align: center; margin: 0px 100px; font-size: 15px">
                                                <span>Driver Sign</span>
                                                <span>Cash Rec. Sign</span>
                                                <span>GM</span>
                                                <span>Director</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div><!--end of trip-sheet-->
                        @endif

                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row-->
        
    </div>
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

            $('#btnPrint').click(function () {
                if({{$routeId}} == 0) {
                    Swal.fire('Attention!','Please Select Route','warning');
                }
                else if({{count($dataRows)}} == 0) {
                    Swal.fire('Sorry!','No Data Found to Print','warning');
                }
                else {
                    var originalContents = $('body').html();
                    var printContents = $('#trip-sheet').html();
                    $('body').html(printContents);
                    window.print();
                    $('body').html(originalContents);
                }
            });

            var table = $('#reportTable');
            var parent = table.parent();

            if (table.outerWidth() > parent.innerWidth()) {
                $(window).on('load', function() {
                    $("body").toggleClass("enlarge-menu");
                });
            }
        });  
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop