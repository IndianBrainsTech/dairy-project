@extends('app-layouts.admin-master')

@section('title', 'Customer Statement Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        @media print {
            @page {
                size: A4;
                margin: 36pt;
            }
        }

        .align-top {
            vertical-align: top;
        }

        .align-bottom {
            vertical-align: bottom;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Customer Statement Report @endslot
                    @slot('item1') Reports @endslot
                    @slot('item2') Customer Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{route('report.customer.statement')}}">
                            <div class="row">
                                @csrf
                                <label class="my-text mx-2">From</label>
                                <input type="date" name="from_date" id="dt-from" value="{{ $dates['from'] }}" class="my-control mr-2">
                                <label class="my-text mx-2">To</label>
                                <input type="date" name="to_date" id="dt-to" value="{{ $dates['to'] }}" class="my-control mr-3">
                                <div class="input-group mx-2" style="width:350px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name="customer_name" id="act-customer-name" class="form-control" placeholder="Customer">
                                    <input type="hidden" name="customer_id" id="hdn-customer-id">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-2"/>
                                <button id="btn-print" type="button" class="btn btn-pink btn-sm px-2 mx-2" aria-label="Print" title="Print">&nbsp;<i class="fa fa-print"></i>&nbsp;</button>
                                <button id="btn-export" class="btn btn-pink py-0 px-2 ml-1"><i class="mdi mdi-file-excel font-18"></i></button>
                            </div>
                        </form>
                        <hr/>

                        <div id="report-div">
                            @if($customer)
                                <div class="row">
                                    <div class="col-lg-12">
                                        @include('app-components.print-header-2')
                                        <h2 class="title print-only"><u>CUSTOMER STATEMENT</u></h2>
                                        <h3 class="app-h3 dark-blue">{{ $customer['name'] }}</h3>
                                        <h3 class="app-h3 ">{{ $customer['route'] }}</h3>
                                        <h3 class="app-h3 pb-2">{{ formatDateRangeAsDMY($dates['from'],$dates['to']) }}</h3>
                                    </div>
                                </div>
                            @endif

                            @if($records)
                                <table class="app-table" style="margin-bottom: 40px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Invoice No.</th>
                                            <th class="text-right pr-2">Qty</th>
                                            <th class="text-right pr-2">Inv Amt</th>
                                            <th class="text-right pr-2">Inv Tot Amt</th>
                                            <th class="text-right pr-2">Cash</th>
                                            <th class="text-right pr-2">Bank</th>
                                            <th class="text-right pr-2">Incentive</th>
                                            <th class="text-right pr-2">Deposit</th>
                                            <th class="text-right pr-2">Returns</th>
                                            <th class="text-right pr-2">Discount</th>
                                            <th class="text-right pr-2">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td class="text-center align-top">{{ $record['date'] }}</td>
                                                <td class="text-center">{!! implode("<br/>", array_column($record['invoices'], "invoice_num")) !!}</td>
                                                <td class="text-right pr-2">{!! getArrayValuesWithPrecision($record['invoices'], "qty") !!}</td>
                                                <td class="text-right pr-2">{!! getArrayValuesWithPrecision($record['invoices'], "net_amt")!!}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['invoiceTotal']) }}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['cash']) }}</td>
                                                <td class="text-right pr-2 align-bottom">{!! getReceiptAmountWithBank($record['bankRecords']) !!}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['incentive']) }}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['deposit']) }}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['returns']) }}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['discount']) }}</td>
                                                <td class="text-right pr-2 align-bottom">{{ getTwoDigitPrecision($record['balance']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr class="text-right app-bold">
                                            <td colspan="2" class="pr-3">Total</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['qty']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['invoice']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['invoice']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['cash']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['bank']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['incentive']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['deposit']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['returns']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($totals['discount']) }}</td>
                                            <td class="pr-2">{{ formatIndianNumberWithDecimal($record['balance']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @endif

                            @if(!empty($summary))
                                <table class="app-table" style="margin: auto; width: 40%; min-width: 400px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th colspan="2" class="text-center">Summary</th>
                                        </tr>
                                    </thead>
                                    <tbody class="thead-light">
                                        <tr>
                                            <td class="text-left pl-3">Opening Balance</td>
                                            <td class="text-right pr-3 app-bold">{{ formatIndianNumberWithDecimal($summary['opening']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left pl-2">Total Invoice Amount</td>
                                            <td class="text-right pr-2 app-bold">{{ formatIndianNumberWithDecimal($summary['invoices']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left pl-2">Total Receipt Amount</td>
                                            <td class="text-right pr-2 app-bold">{{ formatIndianNumberWithDecimal($summary['receipts']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left pl-2">Total Return Amount</td>
                                            <td class="text-right pr-2 app-bold">{{ formatIndianNumberWithDecimal($summary['returns']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left pl-2">Total Discount Amount</td>
                                            <td class="text-right pr-2 app-bold">{{ formatIndianNumberWithDecimal($summary['discount']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left pl-2">Closing Balance</td>
                                            <td class="text-right pr-2 app-bold">{{ formatIndianNumberWithDecimal($summary['closing']) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            <div class="print-footer" style="margin: 40pt auto 0 auto; width: 80%;">
                                <table class="bank-table" border="1" style="border-collapse: collapse;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th colspan="4" class="text-center p-2">Account Details</th>
                                        </tr>
                                    </thead>
                                    <tbody class="thead-light">
                                        <tr>
                                            <td class="label">Bank</td>
                                            <td class="content section-divider">{{ $banks[0]->bank_name }}</td>
                                            <td class="label">Bank</td>
                                            <td class="content">{{ $banks[1]->bank_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">A/C No</td>
                                            <td class="content section-divider">{{ $banks[0]->acc_number }}</td>
                                            <td class="label">A/C No</td>
                                            <td class="content">{{ $banks[1]->acc_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Branch</td>
                                            <td class="content section-divider">{{ $banks[0]->branch }}</td>
                                            <td class="label">Branch</td>
                                            <td class="content">{{ $banks[1]->branch }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">IFSC</td>
                                            <td class="content section-divider">{{ $banks[0]->ifsc }}</td>
                                            <td class="label">IFSC</td>
                                            <td class="content">{{ $banks[1]->ifsc }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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

            $('#dt-from').change(function() {
                let date = $(this).val();
                $('#dt-to').attr('min',date);
            });

            $('#btn-print').on("click", function () {
                let originalContents = $('body').html();
                let printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#btn-export').click(function(event) {
                event.preventDefault();
                const count = @json(count($records));
                if(count === 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    const query = {
                        from_date   : $("#dt-from").val(),
                        to_date     : $("#dt-to").val(),
                        customer_id : $("#hdn-customer-id").val(),
                    };
                    const url = "{{ route('export.customer.statement') }}?" + $.param(query);
                    window.location = url;
                }
            });

            $("#dt-from").trigger('change');
        });  
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop