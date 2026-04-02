@extends('app-layouts.admin-master')

@section('title', 'Enquiry')

@section('headerStyle')
    <link href="{{ asset('plugins/filter/magnific-popup.css') }}" rel="stylesheet" type="text/css" />    
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
            @component('app-components.breadcrumb-4')
                @slot('title') View Enquiry @endslot
                @slot('item1') Transactions @endslot
                @slot('item2') Enquiry @endslot
                @slot('item3') Enquiry @endslot
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
                                    <h3 class="pro-title" id="shop" style="color:#fd3c97">{{ $enquiry->shop_name }}</h3>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px; margin-top:16px;">
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
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Enquiried On</div>
                                        <div class="col-md-8" style="color:blue">{{ displayDateTime($enquiry->enq_datetime) }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Enquiried By</div>
                                        <div class="col-md-8" style="color:blue">{{ $enquiry->employee->name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Followup Date</div>
                                        <div class="col-md-8" style="color:blue">{{ displayDate($enquiry->followup_date) }}</div>
                                    </div>
                                </div>
                            </div><!--end col-->  
                            
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">        
                                        <h4 class="mt-0 header-title">Enquiry Location <a id="showMap" href="#" style="color:blue;visibility:hidden"><i class="mdi mdi-google-maps mr-2"></i></a> </h4>
                                        <div id="map-canvas" style="height:300px"></div>
                                    </div>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->
                        <hr/>

                        <h5 style="color:#fd3c97">Competitor Data</h5>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive dash-social">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th>Competitor</th>
                                            <th>Product Data</th>
                                            <th>Offers</th>
                                            <th>Remarks</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($competitor_data as $cdata)
                                                @php
                                                    $data = preg_split('/[-|;]/', $cdata->product_data);
                                                    $prod_data = "";
                                                    for($i=0; $i<count($data); $i++) {
                                                        if($i%3 == 1)
                                                            $prod_data = $prod_data . $data[$i];
                                                        else if($i%3 == 2)
                                                            $prod_data = $prod_data . " : " . $data[$i] . "\n";
                                                    }
                                                @endphp
                                                <tr>                                                
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td>{{ $cdata->competitor->comp_name }}</td>
                                                    <td style="white-space:pre;">{{ $prod_data }}</td>
                                                    <td>{{ $cdata->offers }}</td>                                            
                                                    <td>{{ $cdata->remarks }}</td>
                                                </tr>
                                            @endforeach 
                                        </tbody>
                                    </table>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->
                        <hr/>

                        <h5 style="color:#fd3c97">Photo Uploads</h5>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">                                    
                                    <div class="card-body">
                                        <div class="row container-grid nf-col-3  projects-wrapper">
                                            @foreach($photos as $photo)
                                                <div class="col-lg-4 col-md-6 p-0 nf-item branding design coffee spacing">
                                                    <div class="item-box">
                                                        <a class="cbox-gallary1 mfp-image" href="{{ asset('mystorage/enquiries/' . $photo->name) }}" title="Enquiry Photo">
                                                            <img class="item-container " src="{{ asset('mystorage/enquiries/' . $photo->name) }}" alt="Enquiry Photo" />
                                                            <div class="item-mask" style="background: none repeat scroll 0 0 rgba(80,80,80, 0.4);">
                                                                <div class="item-caption">
                                                                    <h5 class="text-white">{{ $enquiry->shop_name }}</h5>
                                                                    <p class="text-white">{{ getIndiaDateTime($enquiry->created_at) }}</p>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div><!--end item-box-->
                                                </div><!--end col-->
                                            @endforeach
                                        </div><!--end row-->
                                    </div><!--end card-body-->
                                </div><!--end card-->
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
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
                const myLatLng = { lat: {{ $enquiry->latitude }}, lng: {{ $enquiry->longitude }} };
                const map = new google.maps.Map(document.getElementById("map-canvas"), {
                    zoom: 16,
                    center: myLatLng,
                });
                const marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                    title: '{{ $enquiry->shop_name }}'
                });
            }

            $("#showMap").trigger('click'); 
        });
    </script> 
@endpush

@section('footerScript')
    <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA-YK929GUMvcc8vNDURnWl0yRL6kcMXLo"></script>    
    <script src="{{ asset('plugins/filter/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('plugins/filter/masonry.pkgd.min.js') }}"></script>
    <script src="{{ asset('plugins/filter/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.gallery.init.js') }}"></script>
@stop