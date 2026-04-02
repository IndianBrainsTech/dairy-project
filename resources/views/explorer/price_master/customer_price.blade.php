@extends('app-layouts.admin-master')

@section('title', 'Customers')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Customers @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Price Master @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="header-title mt-0">Customers &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($customers) }}
                                    </button>
                                </h4>
                            </div>
                        <div class="col-6">
                        <form action="{{ route('customer.price') }}" method="POST" class="float-right">
                            @csrf
                            <div class="d-flex align-items-center">
                                <label class="mr-2 text-nowrap">Price Master</label>
                                <select name="priceMaster" class="form-control" style="min-width: 100px">
                                    <!-- Option for All price masters -->
                                    <option value="All" @selected(old('priceMaster', $priceMaster) == 'All')>All</option>
                            
                                    <!-- Option for price masters that already exist (linked or available) -->
                                    <option value="Exist" @selected(old('priceMaster', $priceMaster) == 'Exist')>EXIST</option>
                            
                                    <!-- Option for price masters that have not been applied yet (null or unlinked) -->
                                    <option value="Nill" @selected(old('priceMaster', $priceMaster) == 'Nill')>NIL</option>                                                                  
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm ml-2">Submit</button>
                            </div>                            
                        </form>
                    </div>
                    </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="5" class="text-center">S.No</th>                                        
                                        <th data-priority="1" class="text-center">Customer</th>
                                        <th data-priority="2" class="text-center">Price Master</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>                                            
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $customer['name'] }}</td>
                                            <td>
                                            @if($customer['document_number']) 
                                                @foreach($customer['document_number'] as $key => $txn)
                                                    <a href="{{ route('price-masters.show',['master'=>$txn]) }}" target="_blank">{{ $key }}</a>{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            @else
                                                Standard
                                            @endif
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
                    "lengthMenu": [[10, 25, 50, 100,-1], [10, 25, 50, 100,'All']],
                    "pageLength": 25,
                } );
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
