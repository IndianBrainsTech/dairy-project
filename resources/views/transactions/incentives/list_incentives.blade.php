@extends('app-layouts.admin-master')

@section('title', 'List Incentives')

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
                @component('app-components.breadcrumb-3')
                    @slot('title') List Incentives @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('incentives.index') }}">
                            @csrf
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
                                        <th>Date</th>
                                        <th>Number</th>
                                        <th>Customer</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incentives as $incentive)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ displayDate($incentive->incentive_date) }}</td>
                                            <td class="text-center">{{ $incentive->incentive_number }}</td>
                                            <td class="text-left pl-2">{{ $incentive->customer_name }}</td>
                                            <td class="text-center">{{ $incentive->period }}</td>
                                            <td class="text-right pr-2">{{ formatIndianNumber($incentive->net_amount) }}</td>
                                            <td class="text-center">{!! getIncentiveStatusWithBadge($incentive->incentive_status) !!}</td>
                                            <td class="text-center">
                                                <a href="#" class="show mr2" data-incentive="{{ $incentive->incentive_number }}"><i class="dripicons-preview text-primary font-20"></i></a>
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

            function getIncentiveNumbers() {
                let table = $('#datatable').DataTable();
                let incentiveNums = table.column(2,{search:'applied'}).data().toArray();
                return incentiveNums;
            }

            $('body').on('click', '.show', function () {
                let number = $(this).attr('data-incentive');
                let numberList = getIncentiveNumbers();

                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('incentives.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'incentive_number', 'value': number }));
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