@extends('app-layouts.admin-master')

@section('title', 'Employees')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />    
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Employees @endslot
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
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Employees</h4></div>
                            <div style="width:40%;float:left"><a href="{{ route('employees.create') }}" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Employee</a></div>
                        </div>                        
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th data-priority="1">Employee</th>
                                        <th>Code</th>
                                        <th>Role</th>
                                        <th>Mobile Number</th>
                                        <th>Status</th>
                                        <th data-priority="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $employee->name }}</td>
                                            <td>{{ $employee->code }}</td>
                                            <td>
                                                {{ $employee->role->role_name }}
                                                @if($employee->role->short_name)
                                                    ({{ $employee->role->short_name }})
                                                @endif
                                            </td>
                                            <td>{{ $employee->mobile_num }}</td>
                                            <td>
                                                @if($employee->status == "Active")
                                                    <span class="badge badge-md badge-boxed badge-soft-success">{{ $employee->status }}</span>
                                                @else
                                                    <span class="badge badge-md badge-boxed badge-soft-danger">{{ $employee->status }}</span>    
                                                @endif                                                
                                            </td>
                                            <td style="text-align:center">
                                                <a href="{{ route('employees.show',['id'=>$employee->id]) }}" class="mr-2"><i class="dripicons-preview text-primary font-20"></i></a>
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
    <!-- <script src="{{ asset('assets/pages/jquery.datatable.init.js') }}"></script>      -->
@stop