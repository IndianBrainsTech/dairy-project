@extends('app-layouts.admin-master')

@section('title', 'Make Receipt')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .my-text {
            font-size: 15px;
            padding: 6px 6px 6px 0px;
            font-weight: 600;
        }
        .my-link {
            color: orange;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Make Receipt @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Receipts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group row ml-1 mb-4">
                                    <span class="my-text">Route :</span>
                                    <span class="my-text mr-4" style="color:darkBlue">{{$routeName}}</span>
                                    @foreach ($modeTotals as $mode => $total)
                                        <span class="bg-soft-primary rounded mx-2 p-2">{{ $mode }}: <b>{{ $total }}</b></span>
                                    @endforeach
                                    <span class="bg-soft-pink rounded ml-2 p-2">Total: <b>{{ $cumulativeTotal }}</b></span>
                                    <input type="hidden" value="{{$routeId}}" name="routeId" />
                                    <button type="button" id="submit" class="btn btn-primary btn-sm text-light px-3 ml-4">Generate Receipts</button>
                                </div>
                            </div>
                        </div>
 
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm text-center">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Receipt</th>
                                        <th>Customer</th>
                                        <th>Cash</th>
                                        <th>Bank</th>
                                        <th>Incentive</th>
                                        <th>Deposit</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipts as $receipt)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ $receipt->receipt_num }}</td>
                                            <td class="text-left pl-2">{{ $receipt->customer_name }}</td>
                                            <td class="text-right pr-2">@if($receipt->mode == "Cash") {{ $receipt->amount }} @endif</td>
                                            <td class="text-right pr-2">@if($receipt->mode == "Bank") {{ $receipt->amount }} @endif</td>
                                            <td class="text-right pr-2">@if($receipt->mode == "Incentive") {{ $receipt->amount }} @endif</td>
                                            <td class="text-right pr-2">@if($receipt->mode == "Deposit") {{ $receipt->amount }} @endif</td>
                                            <td class="text-center">{!! $receipt->status == "Pending" ? '<span class="my-link">' . e($receipt->status) . '</span>' : e($receipt->status) !!}</td>
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
        var date = @json($date);
    </script>
    <script>
        $(document).ready(function() {
            // Set up AJAX to include CSRF token in the header
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            
            $('#submit').click(function () {
                let id = "{{$routeId}}";
                console.log("routeId : " + id);
                $.get("{{ route('receipts.make.generate', ['routeId' => $routeId]) }}" + "?date=" + date)
                    .done(function(data) {
                        if (data.success) {
                            Swal.fire('Success!', data.message, 'success').then(() => {
                                window.location.href = "{{ route('receipts.make.index') }}" + "?date=" + encodeURIComponent(date);
                            });
                        } else {
                            Swal.fire('Sorry!', data.message, 'warning');
                        }
                    })
                    .fail(function(xhr) {
                        Swal.fire('Sorry!', xhr.responseText, 'error');
                    });
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop