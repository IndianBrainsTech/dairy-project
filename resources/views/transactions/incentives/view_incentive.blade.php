@extends('app-layouts.admin-master')

@section('title', 'View Incentive')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Incentive @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                    @slot('item3') List Incentives @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row">
            <div class="col-12 col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Incentive Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    Incentive Number <br/>
                                    <div class="mt-2">Generated on</div>
                                </div>
                                <div class="col-md-5">
                                    <div class="app-bold">{{ $incentive->number }}</div>
                                    <div class="mt-2">{{ $incentive->date }}</div>
                                </div>
                                <div class="col-md-4 text-right">
                                    @if($incentive->status == "Accepted")
                                        <button id="btn-print" type="button" class="btn btn-pink py-1 px-2 mr-3" aria-label="Print" title="Print">&nbsp;<i class="fa fa-print"></i>&nbsp;</button>
                                    @endif
                                    <button type="button" id="btn-prev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</button>
                                    <button type="button" id="btn-next" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</button>
                                </div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Status</div>
                                <div class="col-md-9 app-bold">{{ $incentive->status }}</div>
                            </div>
                        </div>
                        <hr class="hr1" />

                        <!-- Incentive Table -->
                        <div id="div-incentive" class="table-responsive dash-social px-2">
                            <div class="print-header">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div style="position: relative">
                                            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo" class="title-logo">
                                            <div class="company-info">
                                                <h1 class="company-name">Aasaii Food Productt</h1>
                                                <h3 class="address-line">14-A, Vaiyapurigoundanoor, Uppidamangalam P.O.,</h3>
                                                <h3 class="address-line">Karur - 639114</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="hr1" />
                                <h2 class="title">INCENTIVE STATEMENT</h2>
                                <hr class="hr1" />
                            </div>

                            <h2 class="app-h2">{{ $incentive->customer }}</h2>
                            <h3 class="app-h3 dark-blue">{{ $incentive->route }}</h3>
                            <h3 class="app-h3 pb-2">{{ $incentive->period }}</h3>

                            <table class="app-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-left pl-2">Item Name</th>
                                        <th class="text-right pr-2">Qty</th>
                                        <th class="text-right pr-2">Inc Rate</th>
                                        <th class="text-right pr-2">Inc Amt</th>
                                        <th class="text-right pr-2">Lkg Qty</th>
                                        <th class="text-right pr-2">Lkg Amt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-left pl-2">{{ $record->item_name }}</td>
                                            <td class="text-right pr-2">{{ $record->qty }}</td>
                                            <td class="text-right pr-2">{{ $record->inc_rate }}</td>
                                            <td class="text-right pr-2">{{ $record->inc_amt }}</td>
                                            <td class="text-right pr-2">{{ $record->lkg_qty }}</td>
                                            <td class="text-right pr-2">{{ $record->lkg_amt }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="text-right">
                                    <tr class="thead-light">
                                        <th colspan="2" class="text-center">Total</th>
                                        <th class="pr-2">{{ $summary->qty }}</th>
                                        <th></th>
                                        <th class="pr-2">{{ $summary->inc_amt }}</th>
                                        <th class="pr-2">{{ $summary->lkg_qty }}</th>
                                        <th class="pr-2">{{ $summary->lkg_amt }}</th>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="pr-2">Incentive</td>
                                        <td class="pr-2">{{ $summary->inc_amt }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="pr-2">Leakage</td>
                                        <td class="pr-2">{{ $summary->lkg_amt }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="pr-2">Total</td>
                                        <td class="pr-2">{{ $summary->tot_amt }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="pr-2">TDS</td>
                                        <td class="pr-2">{{ $summary->tds_amt }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="pr-2">Round Off</td>
                                        <td class="pr-2">{{ $summary->round_off }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="pr-2">Net Amount</th>
                                        <th class="pr-2">{{ $summary->net_amt }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
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

            let numberList = @json($number_list).split(',');

            $(document).on('keydown', function(event) {
                if (event.key === 'ArrowLeft') {
                    $('#btn-prev').click();
                }
                else if (event.key === 'ArrowRight') {
                    $('#btn-next').click();
                }
            });

            $('#btn-prev').on("click", function () {
                let index = numberList.indexOf("{{ $incentive->number }}");
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Incentive!','warning');
                }
                else {
                    let number = numberList[index - 1];
                    showIncentive(number);
                }
            });

            $('#btn-next').on("click", function () {
                let index = numberList.indexOf("{{ $incentive->number }}");
                if(index == numberList.length-1) {
                    Swal.fire('Sorry!','No Next Incentive!','warning');
                }
                else {
                    let number = numberList[index + 1];
                    showIncentive(number);
                }
            });

            @if($incentive->status == "Accepted")
                $('#btn-print').on("click", function () {
                    var originalContents = $('body').html();
                    var printContents = $('#div-incentive').html();
                    $('body').html(printContents);
                    window.print();
                    $('body').html(originalContents);
                });
            @endif

            function showIncentive(number) {
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
            }
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop