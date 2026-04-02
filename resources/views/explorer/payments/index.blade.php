@extends('app-layouts.admin-master')

@section('title', 'Bank Payments')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Bank Payments @endslot
                    @slot('item1') Explorer @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <form method="get" action="{{ route('payments.index') }}">
                            <div class="row">
                                <label for="dt-from" class="app-text ml-2">From</label>
                                <input type="date" name="from_date" id="dt-from" 
                                    value="{{ $dates['from'] }}" class="app-control mx-2">

                                <label for="dt-to" class="app-text">To</label>
                                <input type="date" name="to_date" id="dt-to" 
                                    value="{{ $dates['to'] }}" class="app-control mr-3">

                                <label for="ddl-type" class="app-text">Type</label>
                                <select name="type" id="ddl-type" class="app-control mr-3">
                                    <option value="">All</option>
                                    @foreach(\App\Enums\PaymentType::cases() as $option)
                                        <option value="{{ $option->value }}" @selected($option->value === $type)>
                                            {{ $option->label() }}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="ddl-bank" class="app-text">Bank</label>
                                <select name="bank" id="ddl-bank" class="app-control mr-3">
                                    <option value="">All</option>
                                    @foreach($banks as $record)
                                        <option value="{{ $record->id }}" @selected($record->id == $bank)>
                                            {{ $record->display_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="submit" value="Submit" class="btn btn-primary btn-sm px-3 mx-2" 
                                    aria-label="Submit" title="Submit">
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr class="text-center">                                        
                                        <th>S.No</th>
                                        <th>Document</th>
                                        <th>Date</th>
                                        <th>Transaction</th>
                                        <th>Bank</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr class="text-center">                                            
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $record->document_number }}</td>
                                            <td>{{ $record->payment_date }}</td>
                                            <td>{{ $record->payment_type->label() }}</td>
                                            <td>{{ $record->bank_name }}</td>
                                            <td class="text-right pr-2">{{ formatIndianNumber($record->total_amount) }}</td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-link btn-icon btn-view"
                                                        data-document="{{ $record->document_number }}"
                                                        aria-label="View Bank Payment {{ $record->document_number }}">
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
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table= $('#datatable').DataTable( {
                paging: false,
                info: false,
                searching: true,
                dom: 'ft',
            } );

            $('#dt-from').on('change', function () {
                $('#dt-to').attr('min', this.value);
            }).trigger('change');

            $('#datatable').on('click', '.btn-view', function () {
                const document = $(this).data('document');
                const documentList = table.column(1, { search: 'applied' }).data().toArray();

                $('<form>', { method: 'POST', action: "{{ route('payments.show') }}" })
                    .append($('<input>', { type: 'hidden', name: '_token', value: $('meta[name="csrf-token"]').attr('content') }))
                    .append($('<input>', { type: 'hidden', name: 'document', value: document }))
                    .append($('<input>', { type: 'hidden', name: 'document_list', value: documentList }))
                    .appendTo('body')
                    .trigger('submit');
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop