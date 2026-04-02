@extends('app-layouts.admin-master')

@section('title', 'Customer Outstanding')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/input-style.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Customer Outstanding @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Openings @endslot                    
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-10">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th>Customer</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">As per Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td id="name{{$customer->id}}">{{ $customer->customer_name }}</td>
                                            <td style="width:120px"><input type="text" id="amount{{$customer->id}}" value="{{$customer->amount}}" data-value="{{$customer->amount}}" class="form-control amount-cell" maxlength="12" disabled></td>
                                            <td style="width:145px"><input type="date" id="date{{$customer->id}}" value="{{$customer->date}}" data-value="{{$customer->date}}" class="form-control date-cell" disabled></td>
                                            <td class="text-center">
                                                <a href="" id="edit{{$customer->id}}" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="update{{$customer->id}}" class="mr-2 d-none"><i class="fas fa-save text-blue font-16"></i></a>
                                                <a href="" id="clear{{$customer->id}}" class="d-none"><i class="mdi mdi-close-box-outline text-warning font-16"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row--> 
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/customer-finance.js') }}"></script>
    <script>
        function getRouteUrl() {
            return "{{ route('customers.outstanding') }}";
        }
    </script>
@endpush 

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>    
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
