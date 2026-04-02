@extends('app-layouts.admin-master')

@section('title', 'Enquiry')

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
                @component('app-components.breadcrumb-3')
                    @slot('title') Enquiry @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Enquiry @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
 
                        <div style="padding-bottom:16px;padding-top:8px">
                            <span class="header-title mt-0">Enquiries<span>
                            <button type="button" class="btn btn-pink btn-round" style="font-weight:500">{{ count($enquiries) }}</button>
                            <div style="float:right">
                                <form method="post" action="{{ route('enquiries.index') }}">
                                    @csrf
                                    <label class="my-text">From</label>
                                    <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                    <label class="my-text">To</label>
                                    <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                    <label class="my-text">Employee</label>
                                    <select name="empId" class="my-control">
                                        <option value="0" @selected($empId==0)>All</option>
                                        @foreach($employees as $employeeData)
                                            <option value="{{$employeeData->employee->id}}" @selected($empId==$employeeData->employee->id)>{{$employeeData->employee->name}}</option>
                                        @endforeach
                                    </select>
                                    <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm" style="margin-left:16px;padding-left:16px;padding-right:16px;"/>
                                </form><!--end form-->
                            </div>
                        </div>
                        
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr> 
                                    <th data-priority="1">S.No</th>
                                    <th>Employee</th> 
                                    <th>Shop Name</th>                                                                        
                                    <th>Area</th>
                                    <th>Contact Number</th>
                                    <th data-priority="3">Enquiry DateTime</th>
                                    <th data-priority="2">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($enquiries as $enquiry)
                                        @php
                                            $dateTime = new DateTime($enquiry->enq_datetime);
                                            $dateTime = $dateTime->format('d-m-Y h:i A');
                                        @endphp
                                        <tr>                                                
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $enquiry->employee->name }}</td>
                                            <td>{{ $enquiry->shop_name }}</td>
                                            <td>{{ $enquiry->area_name }}</td>
                                            <td>{{ $enquiry->contact_num }}</td>
                                            <td>{{ $dateTime }}</td>
                                            <td style="text-align:center">
                                                <a href="{{ route('enquiries.show',['id'=>$enquiry->id]) }}" class="mr-2"><i class="dripicons-preview text-primary font-20"></i></a>
                                                <a href="#" class="testmap" style="color:gray;float:right" data-lat="{{$enquiry->latitude}}" data-lng="{{$enquiry->longitude}}"><i class="mdi mdi-google-maps mr-2"></i></a>
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
                "pageLength": 25,
            } );

            $('#fromDate').change(function() {
                var date = $(this).val();
                $('#toDate').attr('min',date);
            });

            $('.testmap').click(function(){
                var lat = parseFloat($(this).attr('data-lat'));
                var lng = parseFloat($(this).attr('data-lng'));
                var url = "https://maps.google.com/?q=" + lat + "," + lng;
                window.open(url);
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
    <!-- <script src="{{ asset('assets/pages/jquery.datatable.init.js') }}"></script>      -->    
@stop