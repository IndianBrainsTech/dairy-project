@extends('app-layouts.admin-master')
@section('title', 'Transport Bill - ' . $transportBill->bill_number)
@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') {{ $transportBill->bill_number }} @endslot
            @slot('item1') Transport @endslot @slot('item2') Billing @endslot @slot('item3') Transport Bills @endslot
        @endcomponent
    </div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

    <div class="row">
        <div class="col-12 col-lg-9">
            {{-- Bill Header --}}
            <div class="card mb-3"><div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Bill No:</strong> {{ $transportBill->bill_number }}</div>
                    <div class="col-md-4"><strong>Date:</strong> {{ $transportBill->bill_date->format('d-m-Y') }}</div>
                    <div class="col-md-4"><strong>Type:</strong> {{ ucfirst($transportBill->bill_type) }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Vehicle:</strong> {{ $transportBill->vehicle->vehicle_number ?? '—' }}</div>
                    <div class="col-md-4"><strong>Transporter:</strong> {{ $transportBill->supplierTransporter->name ?? '—' }}</div>
                    <div class="col-md-4"><strong>Period:</strong> {{ $transportBill->bill_period_from->format('d-m-Y') }} to {{ $transportBill->bill_period_to->format('d-m-Y') }}</div>
                </div>
            </div></div>

            {{-- Trip Items Table --}}
            <div class="card mb-3"><div class="card-header"><strong>Trip Details</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light"><tr>
                                <th>Trip No.</th><th>Date</th><th class="text-right">Distance</th>
                                <th class="text-right">Milk (L)</th><th class="text-right">Trip Amt</th>
                                <th class="text-right">Diesel</th><th class="text-right">Adj.</th><th class="text-right">Total</th>
                            </tr></thead>
                            <tbody>
                                @foreach($transportBill->items as $item)
                                <tr>
                                    <td>{{ $item->tripSheet->trip_number ?? '—' }}</td>
                                    <td>{{ $item->trip_date->format('d-m-Y') }}</td>
                                    <td class="text-right">{{ number_format($item->distance_km,2) }}</td>
                                    <td class="text-right">{{ number_format($item->milk_litres,2) }}</td>
                                    <td class="text-right">₹ {{ number_format($item->trip_amount,2) }}</td>
                                    <td class="text-right">₹ {{ number_format($item->diesel_amount,2) }}</td>
                                    <td class="text-right text-danger">₹ {{ number_format($item->adjustment_amount,2) }}</td>
                                    <td class="text-right font-weight-medium">₹ {{ number_format($item->line_total,2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="thead-light"><tr>
                                <td colspan="4" class="text-right font-weight-bold">Total</td>
                                <td class="text-right font-weight-bold">₹ {{ number_format($transportBill->trip_charges,2) }}</td>
                                <td class="text-right font-weight-bold">₹ {{ number_format($transportBill->diesel_charges,2) }}</td>
                                <td class="text-right font-weight-bold text-danger">₹ {{ number_format($transportBill->adjustment_amount,2) }}</td>
                                <td class="text-right font-weight-bold">₹ {{ number_format($transportBill->gross_amount,2) }}</td>
                            </tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            {{-- Amount Summary --}}
            <div class="card mb-3"><div class="card-header"><strong>Amount Summary</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td>Gross Amount</td><td class="text-right">₹ {{ number_format($transportBill->gross_amount,2) }}</td></tr>
                        <tr><td>TDS ({{ $transportBill->tds_percentage }}%)</td><td class="text-right text-danger">- ₹ {{ number_format($transportBill->tds_amount,2) }}</td></tr>
                        <tr class="font-weight-bold"><td>Net Amount</td><td class="text-right">₹ {{ number_format($transportBill->net_amount,2) }}</td></tr>
                        <tr><td>Paid Amount</td><td class="text-right text-success">₹ {{ number_format($transportBill->paid_amount,2) }}</td></tr>
                        <tr class="font-weight-bold text-danger"><td>Balance</td><td class="text-right">₹ {{ number_format($transportBill->balance_amount,2) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card"><div class="card-body">
                @if($transportBill->status === 'draft')
                    <form action="{{ route('transport.transport-bills.approve', $transportBill) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Approve this bill?')">
                            <i class="mdi mdi-check-circle mr-1"></i>Approve Bill
                        </button>
                    </form>
                @endif
                @if($transportBill->balance_amount > 0 && $transportBill->status === 'approved')
                    <button type="button" class="btn btn-info btn-block mb-2" data-toggle="modal" data-target="#paymentModal">
                        <i class="mdi mdi-cash mr-1"></i>Record Payment
                    </button>
                @endif
                <a href="{{ route('transport.transport-bills.index') }}" class="btn btn-secondary btn-block">Back to List</a>
            </div></div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Record Payment</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <form action="{{ route('transport.transport-bills.payment', $transportBill) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p>Balance: <strong>₹ {{ number_format($transportBill->balance_amount,2) }}</strong></p>
                <div class="form-group"><label>Payment Amount <small class="text-danger">*</small></label>
                    <input type="number" name="paid_amount" step="0.01" class="form-control" min="0.01" max="{{ $transportBill->balance_amount }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div></div>
</div>
@stop
@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
@stop
