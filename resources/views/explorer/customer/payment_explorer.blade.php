@extends('app-layouts.admin-master')

@section('title', 'Payment Mode')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Payment Mode @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Customers @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:40%;float:left">
                                <h4 class="header-title mt-0">Payment Mode &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($customers) }}
                                    </button>
                                </h4>
                            </div>
                        </div>
                        <form action="{{ route('payment.explorer') }}" method="POST" class="float-right">
                            @csrf
                            <div class="d-flex align-items-center">
                                <label class="mr-2 text-nowrap">Payment Mode</label>
                                <select name="payment_mode" class="form-control" style="min-width: 100px">
                                    <option value="All" @selected(old('payment_mode', $payment_mode) == 'All')>All</option>
                                    <option value="Cash & Carry" @selected(old('payment_mode',$payment_mode) == 'Cash & Carry')>Cash & Carry (Daily)</option>
                                    <option value="Bill to Bill" @selected(old('payment_mode',$payment_mode) == 'Bill to Bill')>Bill to Bill (Alternate Days)</option>
                                    <option value="Weekly" @selected(old('payment_mode',$payment_mode) == 'Weekly')>Weekly</option>                                                                    
                                    <option value="Twice Monthly" @selected(old('payment_mode',$payment_mode) == 'Monthly')>Twice Monthly</option>
                                    <option value="Monthly" @selected(old('payment_mode',$payment_mode) == 'Monthly')>Monthly</option>                               
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm ml-2">Submit</button>
                            </div>
                        </form>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="5" class="text-center">S.No</th>
                                        <th data-priority="4" class="text-center">Route</th>
                                        <th data-priority="1" class="text-center">Customer</th>
                                        <th data-priority="2" class="text-center">Payment Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $customer->route->name}}</td>
                                            <td>{{ $customer->customer_name }}</td>
                                            <td >{{ $customer->payment_mode }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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

                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100,-1], [10, 25, 50, 100,'All']],
                    "pageLength": 25,
                } );
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
