@extends('app-layouts.admin-master')

@section('title', 'Incentive Master')

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
                @slot('title') View Incentive Master @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Deals & Pricing @endslot
                @slot('item3') Incentive Masters @endslot
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
                                <h6 class="font-14 mb-3" style="color:#fd3c97">Incentive Master Data :</h6>
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

                            <div class="col-lg-6">
                                <h6 class="font-14 mb-3" style="color:#fd3c97">Incentive Data :</h6>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-4">Incentive Type</div>
                                    <div class="col-md-8" style="color:blue">{{ $incentive_type }}</div>
                                </div>
                                @if( $incentive_type == "Fixed")
                                    <div class="row ml-2 mb-2">
                                        <div class="col-md-4">Incentive Rate</div>
                                        <div class="col-md-8" style="color:blue">{{ $incentive_rate }}</div>
                                    </div>
                                @elseif( $incentive_type == "Slab")
                                    <div class="table-responsive table-container">
                                        <table class="table table-bordered table-sm text-center" style="width:260px">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>From</th>
                                                    <th>To</th>
                                                    <th>Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($slab_data as $slab)
                                                    <tr>
                                                        <td>{{ $slab['from'] }}</td>
                                                        <td>{{ $slab['to'] }}</td>
                                                        <td>{{ $slab['rate'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                <div class="table-responsive table-container">
                                    <table class="table table-bordered table-sm text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>S.No</th>
                                                <th class="text-left">Product</th>
                                                @if( $incentive_type == "Fixed")
                                                    <th>Inc Rate</th>
                                                @endif
                                                <th>Lk Qty</th>
                                                <th>Lk Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($incentive_data as $data)
                                                <tr>
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td class="text-left">{{ $data['product'] }}</td>
                                                    @if( $incentive_type == "Fixed")
                                                        <td>{{ $data['inc_rate'] }}</td>
                                                    @endif
                                                    <td>{{ $data['lk_qty'] }}</td>
                                                    <td>{{ $data['lk_amt'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!--end row-->
                        <hr/>

                        <a href="{{ route('incentive-master.edit',['id'=>$id]) }}"><button class="btn btn-gradient-primary px-4 mr-3" type="button">Edit</button></a>
                        @if($status == "Active")
                            <a href="{{ route('incentive-master.status',['id'=>$id]) }}"><button class="btn btn-gradient-danger px-3" type="button">Set Inactive</button></a>
                        @else
                            <a href="{{ route('incentive-master.status',['id'=>$id]) }}"><button class="btn btn-gradient-primary px-3" type="button">Set Active</button></a>
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