@extends('app-layouts.admin-master')

@section('title', 'Price Explorer')

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
                    @slot('title') Price Explorer @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Products @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-10">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%;float:left">
                                <h4 class="header-title mt-0">Price Explorer &nbsp;
                                    <button type="button" class="btn btn-pink btn-round" style="font-weight:500">
                                        {{ count($products) }}
                                    </button>
                                </h4>
                            </div>
                        </div>
                        <div class="float-right">
                            <div class="btn-group btn-group-toggle mb-4" data-toggle="buttons">                                
                                <label class="btn btn-outline-secondary">
                                    <input type="radio" name="options" id="all-units-btn" checked=""> All Units
                                </label>    
                                <label class="btn btn-outline-secondary active">
                                    <input type="radio" name="options" id="prime-units-btn"> Prime Units Only
                                </label>                            
                            </div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="6" class="text-center">S.No</th>
                                        <th data-priority="2">Group</th>
                                        <th data-priority="1">Product</th>
                                        <th data-priority="3">Unit</th>
                                        <th data-priority="4">Price</th>
                                        <th data-priority="5">Conversion</th>
                                    </tr>
                                </thead>
                                <tbody id="product-table-body">
                                    @foreach($products as $product)
                                        @foreach($product->conversion as $index => $conversion)
                                            <tr>
                                                @if ($index === 0)
                                                    <td class="text-center" rowspan="{{ count($product->conversion) }}">{{ $loop->parent->index + 1 }}</td>
                                                    <td rowspan="{{ count($product->conversion) }}">{{ $product->prod_group->name }}</td>
                                                    <td rowspan="{{ count($product->conversion) }}">{{ $product->name }}</td>
                                                @endif
                                                <td class="{{ $index === 0 ? '' : 'others' }}">{{ $conversion->unit_name }}</td>
                                                <td class="{{ $index === 0 ? '' : 'others' }}">{{ "Rs. " . $conversion->price }}</td>
                                                <td class="{{ $index === 0 ? '' : 'others' }}">{{ $conversion->conversion }}</td>
                                            </tr>
                                        @endforeach                                
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

            $('#prime-units-btn').change(function () {
                if(this.checked) {
                    $('.others').hide();
                }
            });

            $('#all-units-btn').change(function () {
                if(this.checked) {
                    $('.others').show();
                }
            });
            
        }); 

    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
