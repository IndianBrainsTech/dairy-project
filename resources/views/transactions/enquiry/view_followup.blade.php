@extends('app-layouts.admin-master')

@section('title', 'Followup')

@section('headerStyle')
    <style type="text/css">
        #map-canvas1, #map-canvas2{
            width:100%;
            height:300px;
        }

        #map-canvas3 {
            width:500px;
            height:400px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
            @component('app-components.breadcrumb-4')
                @slot('title') View Followup @endslot
                @slot('item1') Transactions @endslot
                @slot('item2') Enquiry @endslot
                @slot('item3') Followup @endslot
            @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="single-pro-detail">
                                    <h3 class="pro-title" id="shop" style="color:#fd3c97">{{ $followup->enquiry->shop_name }}</h3>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Area</div>
                                        <div class="col-md-8" style="color:blue">{{ $followup->enquiry->area_name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Followup By</div>
                                        <div class="col-md-8" style="color:blue">{{ $followup->employee->name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Date Time</div>
                                        <div class="col-md-8" style="color:blue">{{ displayDateTime($followup->followup_datetime) }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Remarks</div>
                                        <div class="col-md-8" style="color:blue">{{ $followup->remarks }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Status</div>
                                        <div class="col-md-8" style="color:blue">{{ $followup->followup_status }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Next Visit Date</div>
                                        <div class="col-md-8" style="color:blue">{{ displayDate($followup->next_visit_date) }}</div>
                                    </div>                                    
                                </div>
                            </div><!--end col-->  
                            
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">        
                                        <h4 class="mt-0 header-title">Followup Location <a id="showMap" href="#" style="color:blue;visibility:hidden"><i class="mdi mdi-google-maps mr-2"></i></a> </h4>
                                        <div id="map-canvas1"></div>
                                    </div><!--end card-body-->
                                </div><!--end card-->
                            </div><!--end col-->
                        </div><!--end row-->
                        <hr/>
                        
                        <h5 id="shop_info" style="color:#fd3c97">
                            Shop Information
                            <div class="custom-control custom-switch switch-pink" style="display:inline; margin-left:10px">
                                <input type="checkbox" class="custom-control-input" id="switch_shop" checked>
                                <label class="custom-control-label" for="switch_shop" id="labelSwitchShop">SHOW</label>
                            </div>
                        </h5>
                        
                        <div class="row" id="shop_info_div">
                            <div class="col-lg-6">
                                <div class="single-pro-detail">                                    
                                    <div class="row" style="margin-bottom:10px; margin-left:16px; margin-top:10px">
                                        <div class="col-md-4">Shop Name</div>
                                        <div class="col-md-8" style="color:blue">{{ $enquiry->shop_name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Shop Type</div>
                                        <div class="col-md-8" style="color:blue">{{ $enquiry->shop_type }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Area</div>
                                        <div class="col-md-8" style="color:blue">{{ $enquiry->area_name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Address</div>
                                        <div class="col-md-8" style="color:blue">{{ $enquiry->address }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Landmark</div>
                                        <div class="col-md-8" style="color:blue">{{ $enquiry->landmark }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-5">Contact Number(s)</div>
                                        <div class="col-md-7" style="color:blue; margin-left:-38px">
                                            {{ $enquiry->contact_num }}
                                            @if(!is_null($enquiry->contact_name))
                                                ({{ $enquiry->contact_name }})
                                            @endif
                                        </div>
                                    </div>
                                    @if(!is_null($enquiry->alternate_num))
                                        <div class="row" style="margin-bottom:10px; margin-left:16px; margin-top:-4px">
                                            <div class="col-md-4"></div>
                                            <div class="col-md-8" style="color:blue">
                                                {{ $enquiry->alternate_num }}
                                                @if(!is_null($enquiry->alternate_name))
                                                    ({{ $enquiry->alternate_name }})
                                                @endif
                                            </div>
                                        </div>
                                    @endif                                    
                                </div>
                            </div><!--end col-->  
                            
                            <div class="col-lg-6">
                                <div class="card" style="margin-top:-36px">
                                    <div class="card-body">        
                                        <h4 class="mt-0 header-title">Shop Location</h4>
                                        <div id="map-canvas2"></div>
                                    </div><!--end card-body-->
                                </div><!--end card-->
                            </div>
                        </div><!--end row-->
                        <hr/>

                        <h5 id="history" style="color:#fd3c97">
                            Followup History
                            <div class="custom-control custom-switch switch-pink" style="display:inline; margin-left:10px">
                                <input type="checkbox" class="custom-control-input" id="switch_history" checked>
                                <label class="custom-control-label" for="switch_history" id="labelSwitchHistory">SHOW</label>
                            </div>
                        </h5>
                        
                        <div class="row" id="history_div">
                            <div class="col-lg-12">
                                <div class="table-responsive dash-social">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th>Date & Time</th>
                                            <th>Employee</th>
                                            <th>Remarks</th>
                                            <th>Status</th>
                                            <th>Next Visit</th>                                                                        
                                            <th>Location</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($history as $fhistory)
                                                <tr>                                                
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td>{{ displayDateTime($fhistory->followup_datetime) }}</td>
                                                    <td>{{ $fhistory->employee->name }}</td>
                                                    <td>{{ $fhistory->remarks }}</td>                                            
                                                    <td>{{ $fhistory->followup_status }}</td>
                                                    <td>{{ displayDate($fhistory->next_visit_date) }}</td>
                                                    <td><a class="map-modal" href="#myMapModal" style="color:blue" data-toggle="modal" data-animation="bounce" data-lat="{{$fhistory->latitude}}" data-lng="{{$fhistory->longitude}}"><i class="mdi mdi-google-maps mr-2"></i></a></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->
                        <hr/> 

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
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
                            <div id="map-canvas3"></div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>

@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <!-- <script src="{{ asset('assets/pages/jquery.my-map.js') }}"></script> -->
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }                    
            });

            $('#switch_shop').change(function () {
                if(this.checked) {
                    $("#shop_info_div").show();
                    $("#labelSwitchShop").html('SHOW');
                }
                else {
                    $("#shop_info_div").hide();
                    $("#labelSwitchShop").html('HIDE');
                }
            });

            $('#switch_history').change(function () {
                if(this.checked) {
                    $("#history_div").show();
                    $("#labelSwitchHistory").html('SHOW');
                }
                else {
                    $("#history_div").hide();
                    $("#labelSwitchHistory").html('HIDE');
                }
            });
            
            $('#showMap').click(function(){
                var lat = {{ $followup->latitude }};
                var lng = {{ $followup->longitude }};
                initMap(lat,lng,'map-canvas1');

                lat = {{ $enquiry->latitude }};
                lng = {{ $enquiry->longitude }};
                initMap(lat,lng,'map-canvas2');
            });

            $('.map-modal').click(function(){
                var lat = parseFloat($(this).attr('data-lat'));
                var lng = parseFloat($(this).attr('data-lng'));
                initMap(lat,lng,'map-canvas3');
            });

            function initMap(lati,lngi,canvas) {
                var myLatLng = {lat:lati, lng:lngi};        
                var map = new google.maps.Map(document.getElementById(canvas), {
                    zoom: 16,
                    center: myLatLng
                });
            
                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });
            }

            $("#showMap").trigger('click');
            $("#switch_shop").trigger('change'); 
            $("#switch_history").trigger('change');
        });
    </script> 
@endpush

@section('footerScript')
    <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDeyGhUI-IMTft5Z_O342XQ4oyZdlGcvs8"></script>
@stop