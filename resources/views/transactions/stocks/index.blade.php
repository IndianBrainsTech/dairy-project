@extends('app-layouts.admin-master')

@section('title', 'View Stocks')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Stocks @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Stocks @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <form method="get" action="{{ route('stocks.index') }}" aria-label="Stock date filter">
                            <div class="row">
                                <input type="date" name="from_date" id="dt-from" value="{{ request('from_date', $dates['from']) }}" class="app-control mx-2">
                                <input type="date" name="to_date" id="dt-to" value="{{ request('to_date', $dates['to']) }}" class="app-control mr-3">
                                <input type="submit" value="Submit" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Submit" title="Submit">
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Date</th>
                                        <th>Document</th>
                                        <th>Status</th>
                                        @can('show_stock') <th>Action</th> @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stocks as $stock)
                                        <tr class="text-center">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ displayDate($stock->document_date) }}</td>
                                            <td>{{ $stock->document_number }}</td>
                                            <td>{!! getStatusWithBadge($stock->status->label()) !!}</td>
                                            @can('show_stock') 
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-link btn-icon"
                                                            data-document="{{ $stock->document_number }}"
                                                            aria-label="View document {{ $stock->document_number }}">
                                                        <i class="dripicons-preview text-primary font-20"></i>
                                                    </button>
                                                </td>
                                             @endcan
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
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

            const table = $('#datatable').DataTable({
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,                
                language: {
                    emptyTable: "No stocks found for the date range."
                }
            });

            $('#dt-from').on('change', function () {
                $('#dt-to').attr('min', this.value);
            }).trigger('change');

            @can('show_stock')
                $('#datatable').on('click', 'button[data-document]', function () {
                    const number = $(this).data('document');
                    const numberList = table.column(2,{search:'applied'}).data().toArray();

                    // Create a form element
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': "{{ route('stocks.show') }}"
                    });

                    // Add CSRF token
                    const csrfToken = $('meta[name="csrf-token"]').attr('content');
                    form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                    // Add the data as hidden inputs
                    form.append($('<input>', { 'type': 'hidden', 'name': 'number', 'value': number }));
                    form.append($('<input>', { 'type': 'hidden', 'name': 'number_list', 'value': numberList }));

                    // Append the form to the body and submit it
                    $('body').append(form);
                    form.submit();
                });
            @endcan
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop