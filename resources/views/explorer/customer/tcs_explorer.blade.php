@extends('app-layouts.admin-master')

@section('title', 'TCS Status')

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
                    @slot('title') TCS Status @endslot
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
                                <h4 class="header-title mt-0">TCS Status &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($customers) }}
                                    </button>
                                </h4>
                            </div>
                        </div>
                        <form action="{{ route('tcs.explorer') }}" method="POST" class="float-right">
                            @csrf
                            <div class="d-flex align-items-center">
                                <label class="mr-2 text-nowrap">TCS Status</label>
                                <select name="tcs_status" class="form-control" style="min-width: 100px">
                                    <option value="All" @selected(old('tcs_status', $tcs_status) == 'All')>All</option>
                                    <option value="TCS Applicable" @selected(old('tcs_status',$tcs_status) == 'TCS Applicable')>TCS Applicable</option>
                                    <option value="TCS Applied" @selected(old('tcs_status',$tcs_status) == 'TCS Applied')>Already in TCS</option> 
                                    <option value="TCS Not Applicable" @selected(old('tcs_status',$tcs_status) == 'TCS Not Applicable')>TCS Not Applicable</option>                                  
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
                                        <th data-priority="2" class="text-center">TCS Status</th>
                                        <th data-priority="3" class="text-center">PAN Number</th>
                                        <th data-priority="4" class="text-center" style="min-width: 80px">TCS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $customer->route->name }}</td>
                                            <td>{{ $customer->customer_name }}</td>
                                            <td>{{ $customer->tcs_status == "TCS Applied" ? "Already in TCS" : $customer->tcs_status }}</td>
                                            <td>{{ $customer->pan_number }}</td>
                                            <td class="text-center">
                                                @if($customer->tcs_status != "TCS Not Applicable")
                                                    {{ $customer->pan_number ? $tcsMaster->with_pan . "%" : $tcsMaster->without_pan . "%" }}
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
