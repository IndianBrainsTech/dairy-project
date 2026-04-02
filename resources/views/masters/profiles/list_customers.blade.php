@extends('app-layouts.admin-master')

@section('title', 'Customers')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />    
    <style type="text/css">
        .my-control {
            border: 1px solid #e8ebf3; 
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:20px;
            width:125%;
        }   
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Customers @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Profiles @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">                                                    
                        <div style="width:100%">
                            <div style="width:60%;float:left">
                                <h4 class="header-title mt-0">Customers &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($customers) }}
                                    </button>
                                </h4>
                            </div>
                            <div style="width:40%;float:left"><a href="{{ route('customers.create') }}" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Customer</a></div>
                        </div>                                                
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr class="d-none">
                                        <th class="text-center">S.No</th> 
                                        <th>ID</th>
                                        <th>Customer</th>                                        
                                        <th>Group</th>
                                        <th>Route</th>
                                        <th>Payment Mode</th>
                                        <th>Contact</th>                                        
                                        <th>Action</th>
                                    </tr>
                                    <tr>
                                        <th>S.No</th>
                                        <th>ID</th>
                                        <th>Customer</th>                                        
                                        <th>Group</th>
                                        <th>Route</th>
                                        <th>Payment Mode</th>
                                        <th>Contact</th>                                        
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $customer->customer_code }}</td>
                                            <td>{{ $customer->customer_name }}</td>                                            
                                            <td>{{ $customer->group }}</td>
                                            <td>{{ $customer->route->name }}</td>
                                            <td>{{ $customer->payment_mode }}</td>
                                            <td>{{ $customer->contact_num }}</td>
                                            <td style="text-align:center">
                                                <a href="{{ route('customers.show',['id'=>$customer->id]) }}" class="mr-2"><i class="dripicons-preview text-primary font-20"></i></a>
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
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }                    
            });
            
            // Setup - add a text input to each footer cell
            $('#datatable thead tr:nth-child(2) th').each( function (i) {
                var title = $('#datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" class="my-control" placeholder="'+title+'" data-index="'+i+'" />' );
            } );
                
            // DataTable
            var table = $('#datatable').DataTable( {
                // scrollY:        "300px",
                // scrollX:        true,
                // scrollCollapse: true,
                // paging:         false,
                // fixedColumns:   true
                pageLength: 25
            } );
 
            // Filter event handler
            $( table.table().container() ).on('keyup', 'thead tr:nth-child(2) input', function () {
                table
                    .column( $(this).data('index') )
                    .search( this.value )
                    .draw();
            } );
        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>  
    <!-- Required datatable js -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>    
    <!-- Responsive examples -->
    <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>    
@stop