@extends('app-layouts.admin-master')

@section('title', 'List Credit Notes')

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
                    @slot('title') List Credit Notes @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Credit Notes @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">

                        <form method="get" action="{{ route('credit-notes.index') }}">                            
                            <div class="row">
                                <input type="date" name="from_date" id="dt-from" value="{{ $dates['from'] }}" class="app-control mx-2">
                                <input type="date" name="to_date" id="dt-to" value="{{ $dates['to'] }}" class="app-control mr-3">
                                <div class="input-group mx-2" style="width:380px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name="customer_name" id="act-customer-name" class="form-control" placeholder="Customer">
                                    <input type="hidden" name="customer_id" id="hdn-customer-id">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Submit" title="Submit">
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Document</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $record->document_number }}</td>
                                            <td class="text-center">{{ $record->document_date_for_display }}</td>
                                            <td class="text-left pl-2">{{ $record->customer->customer_name }}</td>
                                            <td class="text-left pl-2">{{ $record->reason->label() }}</td>
                                            <td class="text-center">{!! getStatusWithBadge($record->status->label()) !!}</td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-link btn-icon"
                                                        data-document="{{ $record->document_number }}"
                                                        aria-label="View credit note {{ $record->document_number }}">
                                                    <i class="dripicons-preview text-primary font-20"></i>
                                                </button>
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
        const CUSTOMERS_BY_ROUTE_URL = @json(route('customers.get.route', ':id'));
        const selectedCustomer = @json($customer);
    </script>
    <script src="{{ asset('assets/js/customer-selection5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 25,
            } );

            $('#dt-from').change(function() {
                let date = $(this).val();
                $('#dt-to').attr('min',date);
            });

            $("#dt-from").trigger('change');

            function getDocumentNumbers() {
                let table = $('#datatable').DataTable();
                let documentNumbers = table.column(1,{search:'applied'}).data().toArray();
                return documentNumbers;
            }

            $('#datatable').on('click', 'button[data-document]', function () {
                const currentDocument = $(this).attr('data-document');
                const documentList = getDocumentNumbers();

                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('credit-notes.navigate') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'current_document', 'value': currentDocument }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'document_list', 'value': documentList }));

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