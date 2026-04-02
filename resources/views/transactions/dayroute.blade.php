@extends('app-layouts.admin-master')

@section('title', 'Day Route')

@section('headerStyle')
    <style type="text/css">
        #map-canvas {
            width:100%;
            height:800px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Day Route @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Attendance @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-9">
                            <h4 class="header-title mt-0" style="margin-bottom:16px;color:blue">{{$employee->emp_name}} [{{$employee->emp_code}}] - {{$employee->role_name}}<a href="#" id="showMap" style="visibility:hidden"><i class="mdi mdi-google-maps mr-2"></i></a></h4>
                                <div id="map-canvas"></div>
                            </div>
                            <div class="col-lg-3">
                                <h4 class="header-title mt-0 mb-3" style="color:blue">{{displayDate($date)}}</h4> 
                                <div class="slimscroll activity-scroll">
                                    <div class="activity">
                                      	@php $i=1; @endphp
                                        @foreach($location_data as $data)
                                            @if($data->tag!="Route" && $data->latitude!=0 && $data->longitude!=0)
                                                <div class="activity-info">
                                                    <div class="icon-info-activity">
                                                        @if($data->tag == "Login") <i class="mdi bg-soft-success" style="font-style:normal">
                                                        @elseif($data->tag == "Logout") <i class="mdi bg-soft-warning" style="font-style:normal">
                                                        @elseif($data->tag == "Enquiry") <i class="mdi bg-soft-pink" style="font-style:normal">
                                                        @elseif($data->tag == "Followup") <i class="mdi bg-soft-purple" style="font-style:normal">
                                                        @else <i class="mdi bg-soft-success" style="font-style:normal">
                                                        @endif
                                                      {{$i++}}</i>
                                                    </div>
                                                    <div class="activity-info-text">
                                                        <h6>{{$data->tag}} - {{getIndiaTime($data->created_at)}}</h6>
                                                        <p class="mb-1">{{$data->title}}</p>
                                                        @if($data->description)
                                                            <p class="text-muted">{{$data->description}}</p>
                                                        @endif    
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach                                                                                  
                                    </div><!--end activity-->
                                </div><!--end activity-scroll-->
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

            $('#showMap').click(function(){
                initMap();
            });

            function initMap() {                
                const map = new google.maps.Map(document.getElementById("map-canvas"));
                var infoWindow = new google.maps.InfoWindow(), marker, i;
                var bounds = new google.maps.LatLngBounds(); // for centering map with markers

              	@php $i=1; @endphp
                @foreach($location_data as $data)                    
                    @if($data->tag!="Route" && $data->latitude!=0 && $data->longitude!=0)
                        @php
                            $label = $i++;                            
                        @endphp                        

                        var myLatLng = new google.maps.LatLng({{$data->latitude}}, {{$data->longitude}});
                        marker = new google.maps.Marker({
                            position: myLatLng,
                            map: map,
                            label: '{{$label}}',
                            title: '{{$data->title}}'
                        });
                        bounds.extend(myLatLng);
                        
                        // Add info window to marker    
                        google.maps.event.addListener(marker, 'click', (function(marker, i) {
                            return function() {
                                infoWindow.setContent('<b style="color:magenta;">{{$data->tag}}</b><br/><b style="color:blue;">{{$data->title}}</b>');
                                infoWindow.open(map, marker);
                            }
                        })(marker, i));
                    @endif
                @endforeach
                
                const locationCoordinates = [
                    @foreach($location_data as $data)
                        { lat: {{$data->latitude}}, lng: {{$data->longitude}} },
                    @endforeach
                ];

                const drawPath = new google.maps.Polyline({
                    path: locationCoordinates,
                    geodesic: true,
                    strokeColor: "#0000FF",
                    strokeOpacity: 0.8,
                    strokeWeight: 2.0,
                });

                drawPath.setMap(map);
                map.fitBounds(bounds);
            }

            $("#showMap").trigger('click');
        });  
    </script>
@endpush 

@section('footerScript')
    <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA-YK929GUMvcc8vNDURnWl0yRL6kcMXLo"></script>
    <script type="text/javascript">
        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@stop