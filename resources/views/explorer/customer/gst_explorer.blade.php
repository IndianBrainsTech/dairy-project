@extends('app-layouts.admin-master')

@section('title', 'GST Type')

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
                    @slot('title') GST Type @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Customers @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%;float:left">
                                <h4 class="header-title mt-0">GST Type &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($customers) }}
                                    </button>
                                </h4>
                            </div>
                        </div>
                        <form action="{{ route('gst.explorer') }}" method="POST">
                            @csrf
                            <div class="d-flex align-items-center">
                                <label class="mr-2 text-nowrap">GST Type</label>
                                <select name="gst_type" class="form-control">
                                    <option value="All" @selected(old('gst_type', $gst_type) == 'All')>All</option>
                                    <option value="Intrastate Registered" @selected(old('gst_type', $gst_type) == 'Intrastate Registered')>
                                        Intrastate Registered (Tamilnadu)
                                    </option>
                                    <option value="Intrastate Unregistered" @selected(old('gst_type', $gst_type) == 'Intrastate Unregistered')>
                                        Intrastate Unregistered (Tamilnadu)
                                    </option>
                                    <option value="Interstate Registered" @selected(old('gst_type', $gst_type) == 'Interstate Registered')>
                                        Interstate Registered (Other State)
                                    </option>
                                    <option value="Interstate Unregistered" @selected(old('gst_type', $gst_type) == 'Interstate Unregistered')>
                                        Interstate Unregistered (Other State)
                                    </option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm ml-2">Submit</button>
                            </div>
                        </form>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="5" class="text-center">S.No</th>
                                        <th data-priority="4" class="text-center">Route</th>
                                        <th data-priority="1" class="text-center">Customer</th>
                                        <th data-priority="2" class="text-center">GST Type</th>
                                        <th data-priority="3" class="text-center">GST Number</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $customer->route->name}}</td>
                                            <td>{{ $customer->customer_name }}</td>
                                            <td >{{ $customer->gst_type }}</td>
                                            <td>{{ $customer->gst_number }}</td>
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
