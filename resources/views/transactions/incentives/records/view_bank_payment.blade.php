@extends('app-layouts.admin-master')

@section('title', 'View Bank Payment')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Bank Payment @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                    @slot('item3') Records @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <div class="px-2 mb-3">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    Document Number <br/>
                                    <div class="mt-2">Date</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="app-bold">{{ $data->document_number }}</div>
                                    <div class="mt-2 app-bold">{{ $data->date }}</div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="button" id="btn-excel" class="btn btn-pink py-0 px-2 mr-3" data-toggle="tooltip" data-placement="top" title="Ctrl + E" aria-label="Excel" title="Excel"><i class="mdi mdi-file-excel font-18"></i></button>
                                    <button type="button" id="btn-prev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</button>
                                    <button type="button" id="btn-next" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</button>
                                </div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Bank</div>
                                <div class="col-md-9 app-bold">{{ $data->bank_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount</div>
                                <div class="col-md-9 app-bold">{{ $data->total_amount }}</div>
                            </div>
                        </div>

                        <table class="app-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th class="text-center">Incentive</th>
                                    <th class="text-center">Period</th>
                                    <th class="text-left pr-2">Customer</th>
                                    <th class="text-right pr-2">Amount</th>
                                    <th class="text-center">Account Number</th>
                                    <th class="text-center">Bank</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        <td class="text-center">{{ $loop->index + 1 }}</td>
                                        <td class="text-center">{{ $record['incentive_number'] }}</td>
                                        <td class="text-center">{{ $record['period'] }}</td>
                                        <td class="text-left pr-2">{{ $record['customer_name'] }}</td>
                                        <td class="text-right pr-2">{{ $record['amount'] }}</td>
                                        <td class="text-center">{{ $record['account_number'] }}</td>
                                        <td class="text-center">{{ $record['bank_name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/file-helper.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let numberList = @json($number_list).split(',');
            $('a[href="#MenuTransactions"]').click();

            $(document).on('keydown', function(event) {
                if (event.ctrlKey && event.key.toUpperCase() === 'E') {
                    event.preventDefault();
                    $('#btn-excel').click();
                }
                if (event.key === 'ArrowLeft') {
                    $('#btn-prev').click();
                }
                else if (event.key === 'ArrowRight') {
                    $('#btn-next').click();
                }
            });

            $('#btn-prev').on("click", function () {
                let index = numberList.indexOf(@json($data->document_number));
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Payment Record!','warning');
                }
                else {
                    let number = numberList[index - 1];
                    showBankPayment(number);
                }
            });

            $('#btn-next').on("click", function () {
                let index = numberList.indexOf(@json($data->document_number));
                if(index == numberList.length-1) {
                    Swal.fire('Sorry!','No Next Payment Record!','warning');
                }
                else {
                    let number = numberList[index + 1];
                    showBankPayment(number);
                }
            });

            $('#btn-excel').on("click", function () {
                const payId = @json($data->id);
                const route = `{{ route('incentives.payouts.bank.download', ['pay_id' => '__PAY_ID__']) }}`;
                const url = route.replace('__PAY_ID__', encodeURIComponent(payId));
                downloadExcel(url);
            });

            function showBankPayment(number) {
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
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop