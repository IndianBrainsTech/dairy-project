@extends('app-layouts.admin-master')

@section('title', 'Attendance')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        #map-canvas {
            width:500px;
            height:400px;
        }
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
                @component('app-components.breadcrumb-2')
                    @slot('title') Attendance @endslot
                    @slot('item1') Transactions @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div style="padding-bottom:32px;padding-top:8px">
                            <span class="header-title mt-0">Attendance<span>
                            <div style="float:right">
                                <form method="post" action="{{route('attendances.index') }}">
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
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr>
                                    <th rowspan="2" style="vertical-align:middle" data-priority="1">S.No</th>
                                    <th rowspan="2" style="vertical-align:middle" data-priority="2">Employee</th>
                                    <th rowspan="2" style="vertical-align:middle">Code</th>
                                    <th rowspan="2" style="vertical-align:middle">Date</th>
                                    <th colspan="2" style="text-align:center">Forenoon</th>
                                    <th colspan="2" style="text-align:center">Afternoon</th>
                                    <th rowspan="2" style="vertical-align:middle" data-priority="3">Route</th>
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
                                        @php
                                            $date = date_create($attendance->attn_date);
                                            $date = $date->format('d-m-Y');
                                            $tmin1 = $tmin2 =  $tmout1 = $tmout2 = "";
                                            $tmin1_lat = $tmin2_lat =  $tmout1_lat = $tmout2_lat = "";
                                            $tmin1_lng = $tmin2_lng =  $tmout1_lng = $tmout2_lng = "";

                                            $attn_session = explode(",",$attendance->attn_session);
                                            $time_in = explode(",",$attendance->time_in);
                                            $time_out = explode(",",$attendance->time_out);
                                            $lat_in = explode(",",$attendance->latitude_in);
                                            $lat_out = explode(",",$attendance->latitude_out);
                                            $lng_in = explode(",",$attendance->longitude_in);
                                            $lng_out = explode(",",$attendance->longitude_out);

                                            for($i=0; $i<count($attn_session); $i++) {
                                                if($attn_session[$i] == "Forenoon") {
                                                    if(array_key_exists($i,$time_in)) $tmin1 = $time_in[$i];
                                                    if(array_key_exists($i,$time_out)) $tmout1 = $time_out[$i];
                                                    if(array_key_exists($i,$lat_in)) $tmin1_lat = $lat_in[$i];
                                                    if(array_key_exists($i,$lat_out)) $tmout1_lat = $lat_out[$i];
                                                    if(array_key_exists($i,$lng_in)) $tmin1_lng = $lng_in[$i];
                                                    if(array_key_exists($i,$lng_out)) $tmout1_lng = $lng_out[$i];
                                                }
                                                else if($attn_session[$i] == "Afternoon") {
                                                    if(array_key_exists($i,$time_in)) $tmin2 = $time_in[$i];
                                                    if(array_key_exists($i,$time_out)) $tmout2 = $time_out[$i];
                                                    if(array_key_exists($i,$lat_in)) $tmin2_lat = $lat_in[$i];
                                                    if(array_key_exists($i,$lat_out)) $tmout2_lat = $lat_out[$i];
                                                    if(array_key_exists($i,$lng_in)) $tmin2_lng = $lng_in[$i];
                                                    if(array_key_exists($i,$lng_out)) $tmout2_lng = $lng_out[$i];
                                                }
                                            }

                                            if(count($attn_session) == 2) {
                                                if($tmout1=="" && $tmout2<>"") {
                                                    $tmout1 = $tmout2;
                                                    $tmout1_lat = $tmout2_lat;
                                                    $tmout1_lng = $tmout2_lng;
                                                    $tmout2 = $tmout2_lat = $tmout2_lng = "";
                                                }
                                            }
                                            
                                            $tmin1 = displayTime($tmin1);
                                            $tmin2 = displayTime($tmin2);
                                            $tmout1 = displayTime($tmout1);
                                            $tmout2 = displayTime($tmout2);
                                        @endphp

                                        <tr>                                                
                                            <td>{{ $loop->index + 1 }} &nbsp;&nbsp;
                                                
                                            </td>
                                            <td>{{ $attendance->employee->name }}</td>
                                            <td>{{ $attendance->employee->code }}</td>                                            
                                            <td>{{ $date }}</td>
                                            <td>
                                                {{ $tmin1 }}
                                                @if($tmin1<>"")
                                                    &nbsp; <a href="#myMapModal" class="map-modal" style="color:blue" data-toggle="modal" data-animation="bounce" data-lat="{{$tmin1_lat}}" data-lng="{{$tmin1_lng}}"><i class="mdi mdi-google-maps mr-2"></i></a>
                                                    <a href="#" class="testmap" style="color:gray;float:right" data-lat="{{$tmin1_lat}}" data-lng="{{$tmin1_lng}}"><i class="mdi mdi-google-maps mr-2"></i></a>
                                                @endif
                                            </td>
                                            <td>{{ $tmout1 }}
                                                @if($tmout1<>"")
                                                    &nbsp; <a href="#myMapModal" class="map-modal" style="color:red" data-toggle="modal" data-animation="bounce" data-lat="{{$tmout1_lat}}" data-lng="{{$tmout1_lng}}"><i class="mdi mdi-google-maps mr-2"></i></a>
                                                @endif
                                            </td>
                                            <td>{{ $tmin2 }}
                                                @if($tmin2<>"")
                                                    &nbsp; <a href="#myMapModal" class="map-modal" style="color:blue" data-toggle="modal" data-animation="bounce" data-lat="{{$tmin2_lat}}" data-lng="{{$tmin2_lng}}"><i class="mdi mdi-google-maps mr-2"></i></a>
                                                @endif
                                            </td>
                                            <td>{{ $tmout2 }}
                                                @if($tmout2<>"")
                                                    &nbsp; <a href="#myMapModal" class="map-modal" style="color:red" data-toggle="modal" data-animation="bounce" data-lat="{{$tmout2_lat}}" data-lng="{{$tmout2_lng}}"><i class="mdi mdi-google-maps mr-2"></i></a>
                                                @endif
                                            </td>
                                            <td style="text-align:center">
                                                <a href="{{ route('dayroute.show',['id'=>$attendance->employee->id, 'date'=>$attendance->attn_date]) }}" class="mr-2"><i class="dripicons-preview text-primary font-20"></i></a>
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
    
    <div class="modal fade" id="myMapModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_map_title">Location Map</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div id="map-canvas"></div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
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
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 25
            } );

            $('.map-modal').click(function(){
                var lat = parseFloat($(this).attr('data-lat'));
                var lng = parseFloat($(this).attr('data-lng'));
                initMap(lat,lng);
            });

            function initMap(lati,lngi) {
                var myLatLng = {lat:lati, lng:lngi};        
                var map = new google.maps.Map(document.getElementById('map-canvas'), {
                    zoom: 14,
                    center: myLatLng
                });
            
                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map
                });
            }

            $('.testmap').click(function(){
                var lat = parseFloat($(this).attr('data-lat'));
                var lng = parseFloat($(this).attr('data-lng'));
                var url = "https://maps.google.com/?q=" + lat + "," + lng;
                window.open(url);
            });

            $('#fromDate').change(function() {
                var date = $(this).val();
                $('#toDate').attr('min',date);
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
    <script src="{{ asset('assets/pages/jquery.datatable.init.js') }}"></script>
    <!-- google maps api -->
    <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDeyGhUI-IMTft5Z_O342XQ4oyZdlGcvs8"></script>
@stop