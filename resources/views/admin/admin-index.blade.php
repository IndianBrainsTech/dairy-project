@extends('app-layouts.admin-master')

@section('title', 'Home')

@section('headerStyle')
    {{-- <link href="{{ URL::asset('plugins/jvectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"> --}}
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Dashboard @endslot
                    @slot('item1') Admin @endslot                    
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-3">
                <div class="card report-card" style="background-color:pink">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-8">
                                <p class="text-dark font-weight-semibold font-14">Products</p>
                                <h3 class="my-3">{{$products}}</h3>                                            
                            </div>
                            <div class="col-4 align-self-center">
                                <div class="report-main-icon bg-light-alt">
                                    <i data-feather="pie-chart" class="align-self-center icon-dual-pink icon-lg"></i>  
                                </div>
                            </div>
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col--> 

            <div class="col-md-6 col-lg-3">
                <div class="card report-card" style="background-color:#41cbd8">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">                                                
                            <div class="col-8">
                                <p class="text-dark font-weight-semibold font-14">Customers</p>
                                <h3 class="my-3">{{$customers}}</h3>                                            
                            </div>
                            <div class="col-4 align-self-center">
                                <div class="report-main-icon bg-light-alt">
                                    <i data-feather="users" class="align-self-center icon-dual-secondary icon-lg"></i>  
                                </div>
                            </div> 
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col--> 

            <div class="col-md-6 col-lg-3">
                <div class="card report-card" style="background-color:#6d81f5">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">                                                
                            <div class="col-8">
                                <p class="text-dark font-weight-semibold font-14">Employees</p>
                                <h3 class="my-3">{{$employees}}</h3>                                            
                            </div>
                            <div class="col-4 align-self-center">
                                <div class="report-main-icon bg-light-alt">
                                    <i data-feather="users" class="align-self-center icon-dual-purple icon-lg"></i>  
                                </div>
                            </div> 
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col--> 

            <div class="col-md-6 col-lg-3">
                <div class="card report-card" style="background-color:#ff9f43">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-8">
                                <p class="text-dark font-weight-semibold font-14">Enquiries</p>
                                <h3 class="my-3">{{$enquiries}}</h3>                                            
                            </div>
                            <div class="col-4 align-self-center">
                                <div class="report-main-icon bg-light-alt">
                                    <i data-feather="briefcase" class="align-self-center icon-dual-warning icon-lg"></i>  
                                </div>
                            </div> 
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col--> 
                                          
        </div><!--end row-->  
        
        <div class="row ">            
            <div class="col-md-6 col-lg-3">
                <div class="card report-card" style="background-color:#41cbd8">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">                                                
                            <div class="col-8">
                                <p class="text-dark font-weight-semibold font-14">Routes</p>
                                <h3 class="my-3">{{$routes}}</h3>                                            
                            </div>
                            <div class="col-4 align-self-center">
                                <div class="report-main-icon bg-light-alt">
                                    <i data-feather="clock" class="align-self-center icon-dual-secondary icon-lg"></i>  
                                </div>
                            </div> 
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col--> 

            <div class="col-md-6 col-lg-3">
                <div class="card report-card" style="background-color:pink">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-8">
                                <p class="text-dark font-weight-semibold font-14">Areas</p>
                                <h3 class="my-3">{{$areas}}</h3>                                            
                            </div>
                            <div class="col-4 align-self-center">
                                <div class="report-main-icon bg-light-alt">
                                    <i data-feather="clock" class="align-self-center icon-dual-pink icon-lg"></i>  
                                </div>
                            </div>
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->              
        </div><!--end row-->
    </div>                   
@stop

@section('footerScript')
    <!-- <script src="{{ URL::asset('plugins/jvectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
    <script src="{{ URL::asset('plugins/jvectormap/jquery-jvectormap-us-aea-en.js') }}"></script> 
    <script src="{{ URL::asset('assets/pages/jquery.analytics_dashboard.init.js') }}"></script>    -->
    <script type="text/javascript">
        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@stop