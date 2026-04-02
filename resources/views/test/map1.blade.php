<!-- Map with Marker -->

@extends('app-layouts.admin-master')

@section('title', 'Test Page - Map1')

@section('headerStyle')
    <style type="text/css">
        #map-canvas {
            width:100%;
            height:500px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Map 1 @endslot
                    @slot('item1') Tools @endslot
                    @slot('item2') Test Page @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <button id="showMap" type="button" class="btn btn-gradient-primary" onclick="initMap()"><i class="mdi mdi-google-maps mr-2"></i></button>
                        <div id="map-canvas"></div>
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

            // Initialize and add the map
            function initMap() {                
                // The location of Uluru
                const uluru = { lat: -25.344, lng: 131.031 };
                // The map, centered at Uluru
                const map = new google.maps.Map(document.getElementById("map-canvas"), {
                    zoom: 7,
                    center: uluru,
                });
                // The marker, positioned at Uluru
                const marker = new google.maps.Marker({
                    position: uluru,
                    map: map,
                });
            }

            // window.initMap = initMap;

            $("#showMap").trigger('click'); 
        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- 
     The `defer` attribute causes the callback to execute after the full HTML
     document has been parsed. For non-blocking uses, avoiding race conditions,
     and consistent behavior across browsers, consider loading using Promises
     with https://www.npmjs.com/package/@googlemaps/js-api-loader.
    --> 
    <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDeyGhUI-IMTft5Z_O342XQ4oyZdlGcvs8&callback=initMap"></script>    
@stop
