@extends('app-layouts.admin-master')

@section('title', 'List Bank Payments')

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
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') List Bank Payments @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                    @slot('item3') Records @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('incentives.records.excel.index') }}">
                            @csrf
                            <div class="row">
                                <input type="date" name="from_date" id="dt-from" value="{{ $dates['from'] }}" class="app-control mx-2">
                                <input type="date" name="to_date" id="dt-to" value="{{ $dates['to'] }}" class="app-control mr-3">
                                <input type="submit" value="Submit" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Submit" title="Submit">
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Number</th>
                                        <th>Date</th>
                                        <th>Bank</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr class="text-center">
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $record->document_number }}</td>
                                            <td>{{ displayDate($record->payment_date) }}</td>
                                            <td>{{ $record->bank_name }}</td>
                                            <td class="text-right pr-2">{{ formatIndianNumber($record->total_amount) }}</td>
                                            <td>
                                                <a href="#" class="show mr2" data-number="{{ $record->document_number }}"><i class="dripicons-preview text-primary font-20"></i></a>
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

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();

                $('#datatable').dataTable( {
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',
                } );

                $('#dt-from').change(function() {
                    let date = $(this).val();
                    $('#dt-to').attr('min',date);
                });

                $("#dt-from").trigger('change');
            }

            function getDocumentNumbers() {
                let table = $('#datatable').DataTable();
                let numbers = table.column(1,{search:'applied'}).data().toArray();
                return numbers;
            }

            $('body').on('click', '.show', function () {
                let number = $(this).attr('data-number');
                let numberList = getDocumentNumbers();

                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('incentives.records.excel.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'number', 'value': number }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'number_list', 'value': numberList }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop