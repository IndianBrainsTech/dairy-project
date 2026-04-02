@extends('app-layouts.admin-master')

@section('title', 'Tax Explorer')

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
                    @slot('title') Tax Explorer @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Products @endslot
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
                                <h4 class="header-title mt-0">Tax Explorer &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($products) }}
                                    </button>
                                </h4>
                            </div>
                        </div>
                        <form action="{{ route('tax.explorer') }}" method="POST" class="float-right">
                            @csrf
                            <div class="d-flex align-items-center">
                                <label class="mr-2 text-nowrap">Tax Type</label>
                                <select name="tax_type" class="form-control" style="min-width: 100px">
                                    <option value="All" @selected(old('tax_type', $tax_type) == 'All')>All</option>
                                    <option value="Taxable" @selected(old('tax_type', $tax_type) == 'Taxable')>
                                        Taxable
                                    </option>
                                    <option value="Exempted" @selected(old('tax_type', $tax_type) == 'Exempted')>
                                        Exempted
                                    </option>                                   
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm ml-2">Submit</button>
                            </div>
                        </form>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="9" class="text-center">S.No</th>
                                        <th data-priority="8" class="text-center">Group</th>
                                        <th data-priority="1" class="text-center">Product</th>
                                        <th data-priority="2" class="text-center">HSN Code</th>
                                        <th data-priority="3" class="text-center">Tax Type</th>
                                        <th data-priority="4" class="text-center">GST</th>
                                        <th data-priority="5" class="text-center">SGST</th>
                                        <th data-priority="6" class="text-center">CGST</th>
                                        <th data-priority="7" class="text-center">IGST</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $product->prod_group->name}}</td>
                                            <td>{{ $product->name }}</td>
                                            <td >{{ $product->hsn_code }}</td>
                                            <td>{{ $product->tax_type }}</td>
                                            <td class="text-center">{{ !empty($product->gst) ? $product->gst ."%" :""}}</td>
                                            <td class="text-center">{{ !empty($product->sgst) ? $product->sgst ."%" : ""}}</td>
                                            <td class="text-center">{{ !empty($product->cgst) ? $product->cgst ."%" : ""}}</td>
                                            <td class="text-center">{{ !empty($product->igst) ? $product->igst ."%" : ""}}</td>
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
                    "pageLength": -1,
                } );
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
