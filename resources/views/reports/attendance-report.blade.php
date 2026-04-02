@extends('app-layouts.admin-master')

@section('title', 'Attendance Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />    
    <style type="text/css">
        .my-text {
            font-size:14px;
            padding:4px;
        }
        .my-control {
            border: 1px solid #e8ebf3; 
            padding:4px;
            /* margin-right:10px; */
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Attendance Report @endslot
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
                            <form method="post" action="{{ route('report.attendance') }}">
                                @csrf
                                <label class="my-text">Employee</label>
                                <select name="empId" id="empId" class="my-control">
                                    <option value="0" @selected($empId==0)>All</option>
                                    @foreach($employees as $employee)
                                        <option value="{{$employee->id}}" @selected($empId==$employee->id)>{{$employee->name ." [" . $employee->code ."]"}}</option>
                                    @endforeach
                                </select>
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 ml-3"/>
                                <button id="btnExport" class="btn btn-outline-pink btn-sm px-3 mx-3"><i class="mdi mdi-file-excel mr-1 font-12"></i>Excel</button>
                            </form><!--end form-->
                        </div>
                        <hr/>
                        
                        <div class="table-responsive dash-social" style="overflow-x:auto">
                            <table id="datatable" class="table table-sm table-bordered nowrap" style="overflow:scroll; width:100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th rowspan="2" style="vertical-align:middle">S.No</th>
                                        <th rowspan="2" style="vertical-align:middle">Employee</th>
                                        <th rowspan="2" style="vertical-align:middle">Code</th>
                                        <th rowspan="2" style="vertical-align:middle">Date</th>
                                        <th colspan="2" style="text-align:center">Forenoon</th>
                                        <th colspan="2" style="text-align:center">Afternoon</th>
                                        <th rowspan="2" style="vertical-align:middle;text-align:center">Elapsed Time <br/> [Session]</th>
                                    </tr>
                                    <tr>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                        <tr>                                                
                                            <td>{{ $attendance['sno'] }} </td>
                                            <td>{{ $attendance['emp_name'] }}</td>
                                            <td>{{ $attendance['emp_code'] }}</td>
                                            <td>{{ $attendance['attn_date'] }}</td>
                                            <td>{{ $attendance['tmin1'] }}</td>
                                            <td>{{ $attendance['tmout1'] }}</td>
                                            <td>{{ $attendance['tmin2'] }}</td>
                                            <td>{{ $attendance['tmout2'] }}</td>
                                            <td>{{ $attendance['elapsed_time'] }}</td>
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
                "pageLength": 25,
            } );

            $('#fromDate').change(function() {
                var date = $(this).val();
                $('#toDate').attr('min',date);
            });

            $('#btnExport').click(function(event) {
                event.preventDefault();
                var table = $('#datatable').DataTable();
                var cnt = table.rows().count();
                if(cnt == 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    var query = {
                        fromDate: $("#fromDate").val(),
                        toDate: $("#toDate").val(),
                        empId: $("#empId").val()
                    };
                    var url = "{{ route('export.attendance') }}?" + $.param(query);
                    window.location = url;
                }
            });

            $("#fromDate").trigger('change');
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