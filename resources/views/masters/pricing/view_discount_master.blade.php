@extends('app-layouts.admin-master')

@section('title', 'Discount Master')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
            @component('app-components.breadcrumb-4')
                @slot('title') View Discount Master @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Deals & Pricing @endslot
                @slot('item3') Discount Masters @endslot
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
                                <h6 class="font-14 mb-3" style="color:#fd3c97">Discount Master Data :</h6>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-4">Txn ID</div>
                                    <div class="col-md-8" style="color:blue">{{ $txn_id }}</div>
                                </div>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-4">Txn Date</div>
                                    <div class="col-md-8" style="color:blue">{{ $txn_date }}</div>
                                </div>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-4">Effect Date</div>
                                    <div class="col-md-8" style="color:blue">{{ $effect_date }}</div>
                                </div>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-4">Narration</div>
                                    <div class="col-md-8" style="color:blue">{{ $narration }}</div>
                                </div>

                                <h6 class="font-14 mt-4 mb-3" style="color:#fd3c97">Applicable Customer(s) :</h6>
                                <div class="table-responsive table-container">
                                    <table id="tableCustomers" class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" width="60px">S.No</th>
                                                <th>Customer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customers as $customer)
                                                <tr>
                                                    <td class="text-center">{{ $loop->index + 1 }}</td>
                                                    <td>{{ $customer['customer_name'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div><!--end col-->  

                            <div class="col-lg-5 ml-4">
                                <h6 class="font-14 mb-3" style="color:#fd3c97">Discount Data :</h6>
                                <div class="table-responsive table-container">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" width="60px">S.No</th>
                                                <th>Product</th>
                                                <th class="text-center">Discount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product_discounts as $product)
                                                <tr>
                                                    <td class="text-center">{{ $loop->index + 1 }}</td>
                                                    <td>{{ $product['name'] }}</td>
                                                    <td class="text-center">{{ $product['discount'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!--end row-->
                        <hr/>

                        <a href="{{ route('discount-master.edit',['id'=>$id]) }}"><button class="btn btn-gradient-primary px-4 mr-3" type="button">Edit</button></a>
                        @if($status == "Active")
                            <a href="{{ route('discount-master.status',['id'=>$id]) }}"><button class="btn btn-gradient-danger px-3" type="button">Set Inactive</button></a>
                        @else
                            <a href="{{ route('discount-master.status',['id'=>$id]) }}"><button class="btn btn-gradient-primary px-3" type="button">Set Active</button></a>
                        @endif
                        
                        @if(Session::has('success'))
                            <div class="alert alert-success" style="width:60%;align:center;margin-top:20px">
                                {{ Session::get('success') }}
                            </div>
                        @endif
                        
                    </div><!--end card-body-->
                </div><!--end card-->                
            </div><!--end col-->
        </div><!--end row-->        

    </div><!-- container -->
@stop

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script> 
@stop