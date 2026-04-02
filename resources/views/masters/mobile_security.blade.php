@extends('app-layouts.admin-master')

@section('title', 'Mobile Security')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />    
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Mobile Security @endslot
                    @slot('item1') Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee</th>
                                    <th>Mobile Number</th>
                                    <th>App Version</th>
                                    <th>Mobile Model</th>
                                    <th>Android Version</th>
                                    <th>Unique Code</th>
                                    <th>Registered on</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($mobileData as $data)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $data->user->name }}</td>
                                            <td>{{ $data->mobile_num }}</td>
                                            <td>{{ $data->app_version }}</td>
                                            <td>{{ $data->model }}</td>
                                            <td>{{ $data->android_version }}</td>
                                            <td>{{ $data->unique_code }}</td>
                                            <td>{{ getIndiaDateTime($data->created_at) }}</td>
                                            <td style="text-align:center">                                                       
                                                <a href="" id="delete_data" data-id="{{ $data->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 25
                } );

            $('body').on('click', '#delete_data', function (event) {
                event.preventDefault();  
                var id = $(this).data('id');                   
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this data!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '$success',
                    cancelButtonColor: '$danger',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then(function(result) {                    
                    if (result.value) {
                        $.ajax({
                            url:'/mobile_data/' + id,
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Mobile Data has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })                         
                    }
                })
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
@stop