@extends('app-layouts.admin-master')

@section('title', 'Stock Register')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Stock Register @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Stocks @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row"> 
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row mx-auto">
                            <button type="button" id="btn-prev" class="btn btn-info px-2" style="padding:3px" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)"> < </button>
                            <input type="date" id="dt-register" value="{{ date('Y-m-d') }}" class="app-control text-center" min="2025-04-01" max="{{ date('Y-m-d') }}">
                            <button type="button" id="btn-next" class="btn btn-info px-2" style="padding:3px" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)"> > </button>
                        </div>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Item Name</th>
                                        <th>Unit</th>
                                        <th>Opening</th>
                                        <th>Production</th>
                                        <th>Sales</th>
                                        <th>Return</th>
                                        <th>Closing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stocks as $stock)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2">{{ $stock->item_name }}</td>
                                            <td class="text-center">{{ $stock->unit->display_name }}</td>
                                            <td class="text-right pr-2">{{ getEmptyForZero($stock->opening_qty) }}</td>
                                            <td class="text-right pr-2">{{ getEmptyForZero($stock->production_qty) }}</td>
                                            <td class="text-right pr-2">{{ getEmptyForZero($stock->sales_qty) }}</td>
                                            <td class="text-right pr-2">{{ getEmptyForZero($stock->return_qty) }}</td>
                                            <td class="text-right pr-2">{{ getEmptyForZero($stock->closing_qty) }}</td>
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
    <script src="{{ asset('assets/js/script-helper.js') }}"></script>
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

                language: {
                    emptyTable: "No stock found for the date."
                },

                columnDefs: [
                    { targets: 0, className: 'text-center' },
                    { targets: 1, className: 'text-left pl-2' },
                    { targets: 2, className: 'text-center' },
                    { targets: 3, className: 'text-right pr-2' },
                    { targets: 4, className: 'text-right pr-2' },
                    { targets: 5, className: 'text-right pr-2' },
                    { targets: 6, className: 'text-right pr-2' },
                    { targets: 7, className: 'text-right pr-2' },
                ]
            });

            let minDate, maxDate;
            doInit();

            function doInit() {
                minDate = new Date($('#dt-register').attr('min'));
                maxDate = new Date($('#dt-register').attr('max'));

                $('#btn-prev').on('click', () => changeDateByDays(-1));
                $('#btn-next').on('click', () => changeDateByDays(1));
                $('#dt-register').on('change', showRegister);
            }

            function changeDateByDays(offset) {
                let currentDate = $('#dt-register').val();
                let date = currentDate ? new Date(currentDate) : new Date();
                date.setDate(date.getDate() + offset);
                if(date < minDate || date > maxDate)
                    return;
                let formatted = date.toISOString().split('T')[0];
                $('#dt-register').val(formatted);
                showRegister();
            }

            function showRegister() {
                const date = $('#dt-register').val();
                $.ajax({
                    url: "{{ route('stocks.register.json') }}",
                    method: "GET",
                    data: { date: date, },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);

                    table.clear(); // clear DataTables-managed rows

                    $.each(response, function(index, stock) {
                        table.row.add([
                            index + 1,
                            stock.item_name,
                            stock.unit.display_name,
                            getEmptyForZero(stock.opening_qty),
                            getEmptyForZero(stock.production_qty),
                            getEmptyForZero(stock.sales_qty),
                            getEmptyForZero(stock.return_qty),
                            getEmptyForZero(stock.closing_qty),
                        ]);
                    });

                    table.draw(false); // redraw while keeping search/pagination
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }            
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop