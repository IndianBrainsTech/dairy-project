@extends('app-layouts.admin-master')

@section('title', 'Current Stock')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Current Stock @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Stocks @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row"> 
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Item Name</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stocks as $stock)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2">{{ $stock->item_name }}</td>
                                            <td class="text-right pr-2">{{ $stock->current_stock }}</td>
                                            <td class="text-left pl-2">{{ $stock->unit->display_name }}</td>
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

            let table = $('#datatable').DataTable({
                paging: false,
                info: false,
                searching: true,
                dom: 'ft',

                // Column definitions
                columnDefs: [
                    { targets: 0, className: 'text-center' },  // S.No
                    { targets: 1, className: 'text-left pl-2' }, // Item Name
                    { targets: 2, className: 'text-right pr-2' }, // Qty
                    { targets: 3, className: 'text-left pl-2' }, // Unit
                ]
            });

            function loadStockData() {
                $.get("{{ route('stocks.current.json') }}", function(data) {
                    table.clear(); // clear DataTables-managed rows

                    $.each(data, function(index, stock) {
                        table.row.add([
                            index + 1,
                            stock.item_name,
                            stock.current_stock,
                            stock.unit.display_name
                        ]);
                    });

                    table.draw(false); // redraw while keeping search/pagination
                });
            }

            // Load every 5 seconds
            setInterval(loadStockData, 5000);
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop