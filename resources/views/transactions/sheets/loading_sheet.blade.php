@extends('app-layouts.admin-master')

@section('title', 'Loading Sheet')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-text {
            font-size:14px;
            padding:4px;
        }
        .my-control {
            border: 1px solid #e8ebf3; 
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right: 20px;
        }
        .para {
            line-height: 1.15;
            font-size: 1.1em;
            font-weight: 400;
            margin-bottom: 6px;
        }
        .para2 {
            line-height: 1.5;
            font-size: 1.1em;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .para3 {
            line-height: 1.5;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 16px;
            display: block;
            text-align: center;
        }
        .hr1 {
            margin-top: 8px;
            margin-bottom: 8px;
            border-top-color: gray;
        }
        .my-table { 
            width: 100%;
            overflow-x: auto;
            border-collapse: collapse;
            border: 1px solid gray;            
        }        
        .my-table thead, .my-table tfoot {
            font-weight: 800;
            background-color: #F9F9F9;
            border: 1px solid gray;
        }
        .my-table td {
            padding: 5px;
        }
        h5 {
            background-color: #f1f5fa; 
            border: 1px solid gray
        }

        /* Hide print header on the screen */
        @media screen {
            .print-header {
                display: none;
            }
        }

        /* Styles for Printing */
        @media print {
            /* Set page size to A4 */
            @page {
                size: A4;
                margin: 50px 100px;
            }

            td {
                color: black;
                font-size: 15px;
            }

            .para {
                color: black;
                font-size: 15px;
            }

            .para2 {
                color: black;
                font-size: 17px;
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
                    @slot('title') Loading Sheet @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Sheets @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
  
        <div class="row"> 
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <form id="frmLoadingSheet" method="get" action="{{ route('sheets.loading-sheet') }}" class="mb-0" >
                        @csrf
                            <div>
                                <label class="my-text">Date <small class="text-danger font-13">*</small></label>
                                <input type="date" name="lsdate" value="{{$date}}" class="my-control" required>
                                <label class="my-text">Type <small class="text-danger font-13">*</small></label>
                                <select name="lstype" id="lstype" class="my-control" required class="form-control @error('lstype') is-invalid @enderror">
                                    <option value="Customer" @selected($lstype=="Customer")>Partywise Sheet</option>
                                    <option value="Route" @selected($lstype=="Route")>Route Sheet</option>
                                </select>
                                <label class="my-text">Route <small class="text-danger font-13">*</small></label>
                                <select name="route" id="route" class="my-control" required class="form-control @error('route') is-invalid @enderror">
                                    <option value="0" @selected($route_id=="0")>Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{$route->id}}" @selected($route_id==$route->id)>{{$route->name}}</option>
                                    @endforeach
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm text-light px-4 mr-2" />
                                <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a>                                
                            </div>
                        </form>
                        <hr/>
 
                        @if($lstype == "Customer" && $route_id == 0)
                            No Route Selected
                        @elseif(($lstype == "Customer" && !$tables) || ($lstype == "Route" && !$route_tables))
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found for this Route.
                            </div>
                        @else
                            <div id="loading-sheet">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="print-header">
                                            <h4 style="text-align:center">Aasaii Food Productt</h4>
                                            <p class="para">14-A, Vaiyapurigoundanoor, Uppidamangalam P.O.,</p>
                                            <p class="para">Karur 639 114.</p>
                                            <p class="para">GST No : 33AANFA9261A1ZP</p>
                                            <p class="para">Cell No : 9842089525</p>
                                            <hr class="hr1"/>
                                        </div>
                                        <div class="pt-2">
                                            <p class="para3">Loading Sheet</p>
                                            <p class="para2 mb-4 text-center">{{ $lstype=="Customer" ? "Party" : "Route" }} wise as on {{ displayDate($date) }}</p>
                                        </div>

                                        @if($lstype == "Customer")
                                            @foreach($tables as $table)
                                                <div class="card">
                                                    <h5 class="text-dark p-2 my-0">Party: {{ $table['customer'] }}</h5>
                                                    <div class="card-body px-0 py-0">
                                                        <table class="my-table">
                                                            <thead>
                                                                <tr class="text-right">
                                                                    <td width="10%" class="text-center">S.No</td>
                                                                    <td class="text-left">Product</td>
                                                                    <td width="10%">Others</td>
                                                                    <td width="12%">Qty</td>
                                                                    <td width="10%">Crates</td>
                                                                    <td width="12%" style="padding-right:16px">Liters</td>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($table['items'] as $item)
                                                                    <tr class="text-right"> 
                                                                        <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                        <td class="text-left">{{ $item['product'] }}</td>
                                                                        <td style="padding-right:16px">{{ $item['others'] }}</td>
                                                                        <td>{{ getTwoDigitPrecision($item['prim_qty']) }}</td>
                                                                        <td style="padding-right:16px">{{ $item['crates'] }}</td>
                                                                        <td style="padding-right:16px">{{ getTwoDigitPrecision($item['liters']) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="text-right">
                                                                    <td colspan="2" class="text-center">Total</td>
                                                                    <td style="padding-right:16px">{{ $table['totals']['others'] }}</td>
                                                                    <td>{{ $table['totals']['qty'] }}</td>
                                                                    <td style="padding-right:16px">{{ $table['totals']['crates'] }}</td>
                                                                    <td style="padding-right:16px">{{ $table['totals']['liters'] }}</td>
                                                                </tr><!--end tr-->
                                                            </tfoot>
                                                        </table><!--end table-->
                                                    </div><!--end card-body-->
                                                </div><!--end card-->
                                            @endforeach
                                            
                                            <div class="card">
                                                <div class="card-body px-0 py-0">
                                                    <table class="my-table">
                                                        <thead>
                                                            <tr class="text-right">
                                                                <td colspan="2"></td>
                                                                <td width="10%">Others</td>
                                                                <td width="12%">Qty</td>
                                                                <td width="10%">Crates</td>
                                                                <td width="12%" style="padding-right:16px">Liters</td>
                                                            </tr>
                                                        </thead>
                                                        <tfoot>
                                                            <tr class="text-right">
                                                                <td colspan="2" class="text-center">Grand Total</td>
                                                                <td style="padding-right:16px">{{ $totals['others'] }}</td>
                                                                <td>{{ $totals['qty'] }}</td>
                                                                <td style="padding-right:16px">{{ $totals['crates'] }}</td>
                                                                <td style="padding-right:16px">{{ $totals['liters'] }}</td>
                                                            </tr><!--end tr-->
                                                        </tfoot>
                                                    </table><!--end table-->
                                                </div><!--end card-body-->
                                            </div><!--end card-->

                                        @elseif($lstype == "Route")
                                            <div class="card">
                                                <div class="card-body px-0 py-0">
                                                    @foreach($route_tables as $routeTable)
                                                        <table class="my-table mb-4">
                                                            <thead>
                                                                <tr style="border: 1px solid gray; height:36px">
                                                                    <td colspan="6" class="pl-4">Route : {{ $routeTable['route'] }}</td>
                                                                </tr>
                                                                <tr class="text-right">
                                                                    <td width="10%" class="text-center">S.No</td>
                                                                    <td class="text-left">Product</td>
                                                                    <td width="10%">Others</td>
                                                                    <td width="12%">Qty</td>
                                                                    <td width="10%">Crates</td>
                                                                    <td width="12%" style="padding-right:16px">Liters</td>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($routeTable['table'] as $data)
                                                                    <tr class="text-right"> 
                                                                        <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                        <td class="text-left">{{ $data['product'] }}</td>
                                                                        <td style="padding-right:16px">{{ $data['others'] }}</td>
                                                                        <td>{{ getTwoDigitPrecision($data['prim_qty']) }}</td>
                                                                        <td style="padding-right:16px">{{ $data['crates'] }}</td>
                                                                        <td style="padding-right:16px">{{ getTwoDigitPrecision($data['liters']) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="text-right">
                                                                    <td colspan="2" class="text-center">Total</td>
                                                                    <td style="padding-right:16px">{{ $routeTable['totals']['others'] }}</td>
                                                                    <td>{{ $routeTable['totals']['prim_qty'] }}</td>
                                                                    <td style="padding-right:16px">{{ $routeTable['totals']['crates'] }}</td>
                                                                    <td style="padding-right:16px">{{ $routeTable['totals']['liters'] }}</td>
                                                                </tr><!--end tr-->
                                                            </tfoot>
                                                        </table><!--end table-->
                                                    @endforeach
                                                    
                                                    @if($summary_table)
                                                        <table class="my-table">
                                                            <thead>
                                                                <tr style="border: 1px solid gray; height:36px">
                                                                    <td colspan="6" class="pl-4">Summary</td>
                                                                </tr>
                                                                <tr class="text-right">
                                                                    <td width="10%" class="text-center">S.No</td>
                                                                    <td class="text-left">Route</td>
                                                                    <td width="10%">Others</td>
                                                                    <td width="12%">Qty</td>
                                                                    <td width="10%">Crates</td>
                                                                    <td width="12%" style="padding-right:16px">Liters</td>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($summary_table['table'] as $data)
                                                                    <tr class="text-right"> 
                                                                        <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                        <td class="text-left">{{ $data['route'] }}</td>
                                                                        <td style="padding-right:16px">{{ $data['others'] }}</td>
                                                                        <td>{{ getTwoDigitPrecision($data['prim_qty']) }}</td>
                                                                        <td style="padding-right:16px">{{ $data['crates'] }}</td>
                                                                        <td style="padding-right:16px">{{ getTwoDigitPrecision($data['liters']) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="text-right">
                                                                    <td colspan="2" class="text-center">Grand Total</td>
                                                                    <td style="padding-right:16px">{{ $summary_table['totals']['others'] }}</td>
                                                                    <td>{{ $summary_table['totals']['prim_qty'] }}</td>
                                                                    <td style="padding-right:16px">{{ $summary_table['totals']['crates'] }}</td>
                                                                    <td style="padding-right:16px">{{ $summary_table['totals']['liters'] }}</td>
                                                                </tr><!--end tr-->
                                                            </tfoot>
                                                        </table><!--end table-->
                                                    @endif
                                                </div><!--end card-body-->
                                            </div><!--end card-->
                                        @endif
                                    </div>
                                </div>
                            </div><!--end of loading-sheet-->
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
                if(!$('#loading-sheet').length) {
                    Swal.fire('Sorry!','No Data Found to Print','warning');
                }
                else {
                    var originalContents = $('body').html();
                    var printContents = $('#loading-sheet').html();
                    $('body').html(printContents);
                    window.print();
                    $('body').html(originalContents);
                }
            });
        });  
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop