@extends('app-layouts.admin-master')

@section('title', 'Shop Conversions')

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
                    @slot('title') Shop Conversions @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Enquiry @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <style type="text/css">
            html, body, #map-canvas  {
                margin: 0;
                padding: 0;
                height: 100%;
            }

            #map-canvas {
                width:500px;
                height:400px;
            }
        </style>

        @if(count($followups) == 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="card" style="background-color:#FEFCFF">
                        <div class="card-body">
                            <h5 style="color:red">No Shops Found for Conversion</h5>
                        </div><!--end card-body-->
                    </div><!--end card-->
                </div><!--end col-->
            </div><!--end row-->
        @endif

        @php($i=0)
        @foreach($followups as $followup)
            @if($i % 4 == 0)
                <div class="row">
            @endif
                <div class="col-lg-3">
                    <div class="card" style="background-color:#FEFCFF">
                        <div class="card-body">
                            <h5 style="color:darkblue"><i class="mdi mdi-shopify"></i>&nbsp;&nbsp;{{$followup->enquiry->shop_name}}</h5>
                            <h6><i class="dripicons-location"></i>&nbsp;&nbsp;{{$followup->enquiry->area_name}}</h6>
                            <h6 style="color:green"><i class="dripicons-phone"></i>&nbsp;&nbsp;{{$followup->enquiry->contact_num}}</h6>
                            <h6 style="color:maroon"><i class="far fa-user"></i>&nbsp;&nbsp;{{$followup->employee->name}}</h6>
                            <h6><i class="mdi mdi-comment-account"></i>&nbsp;&nbsp;{{$followup->remarks}}</h6>
                            <h6 style="margin-bottom:16px"><i class="mdi mdi-timer"></i>&nbsp;&nbsp;{{ displayDateTime($followup->followup_datetime) }}</h6>
                            <a href="#myMapModal" id="map" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-animation="bounce" data-lat="{{$followup->enquiry->latitude}}" data-lng="{{$followup->enquiry->longitude}}"><i class="mdi mdi-google-maps mr-2"></i></a> &nbsp;&nbsp;
                            <a href="{{ route('customers.convert',['id'=>$followup->enquiry->id]) }}" class="btn btn-sm btn-outline-primary">Approve</a>
                        </div><!--end card-body-->
                    </div><!--end card-->
                </div><!--end col-->
            @if($i % 4 == 3)
                </div><!--end row-->
            @endif
            @php($i++)
        @endforeach
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
                            <div id="map-canvas" class=""></div>
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

            $('#map').click(function(){
                var lat = parseFloat($(this).attr('data-lat'));
                var lng = parseFloat($(this).attr('data-lng'));
                initMap(lat,lng);
            });

            function initMap(lati,lngi) {
                var myLatLng = {lat:lati, lng:lngi};        
                var map = new google.maps.Map(document.getElementById('map-canvas'), {
                    zoom: 12,
                    center: myLatLng
                });
            
                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                    title: 'Hello World!'
                });
            }

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
    <!-- Gmaps file -->
    <script src="{{ asset('plugins/gmaps/gmaps.min.js') }}"></script> 
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDeyGhUI-IMTft5Z_O342XQ4oyZdlGcvs8&callback=initMap"></script>       
@stop