@extends('app-layouts.admin-master')

@section('title', 'View Receipts')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .my-text {
            font-size: 14px;
            padding: 4px;
        }
        .my-control {
            border: 1px solid #e8ebf3; 
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:20px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Receipts @endslot
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
                        <form id="frmReceipts" method="get" action="{{ route('receipts.index') }}" class="mb-0" >
                        @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group row ml-1 mb-0">
                                        <label class="my-text">Date</label>
                                        <input type="date" name="date" id="date" value="{{$date}}" class="my-control ml-2" style="border: 1px solid #d3d3d3;border-radius:2px;padding-left:8px;padding-right:8px;">                                                                               
                                        
                                        <label class="my-text">Route <small class="text-danger font-13">*</small></label>
                                        <select name="route" id="route" class="my-control" required class="form-control @error('route') is-invalid @enderror">
                                            <option value="0" @selected($route_id=="0")>Select Route</option>
                                            @foreach($routes as $route)
                                                <option value="{{$route->id}}" @selected($route_id==$route->id)>{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                        <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm text-light px-4 mr-2" />
                                    </div>
                                </div>
                                <div class="row mt-3 ml-1">
                                    @foreach ($modeTotals as $mode => $total)
                                        <span class="bg-soft-primary rounded mx-2 p-2">{{ $mode }}: <b>{{ $total }}</b></span>
                                    @endforeach
                                    <span class="bg-soft-pink rounded ml-2 p-2">Total: <b>{{ $cumulativeTotal }}</b></span>
                                </div>
                            </div>
                        </form>
                        <hr/>
 
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Receipt No.</th>
                                        <th class="text-left pl-2">Route</th>
                                        <th class="text-left pl-2">Customer</th>
                                        <th class="text-right pr-2">Amount</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipts as $receipt)
                                        <tr class="text-center">
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $receipt->receipt_num }}</td>
                                            <td class="text-left pl-2">{{ $receipt->route->name }}</td>
                                            <td class="text-left pl-2">{{ $receipt->customer_name }}</td>
                                            <td class="text-right pr-2">{{ $receipt->amount }}</td>
                                            <td>{{ $receipt->mode }}</td>
                                            <td>{{ $receipt->status }}</td>
                                            <td><a href="#" class="show" data-receipt="{{$receipt->receipt_num}}"><i class="dripicons-preview text-primary font-20"></i></a></td>
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
        $(document).ready(function() {
            // Set up AJAX to include CSRF token in the header
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 25,
            } );

            function getReceiptNumbers() {
                let table = $('#datatable').DataTable();
                let receiptNumbers = table.column(1,{search:'applied'}).data().toArray();
                return receiptNumbers;
            }

            $('body').on('click', '.show', function (event) {
                let receiptNum = $(this).attr('data-receipt');
                let receipts = getReceiptNumbers(); 
                
                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('receipts.show') }}"
                });
 
                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'receipt_num', 'value': receiptNum }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'receipts', 'value': receipts }));

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
@stop