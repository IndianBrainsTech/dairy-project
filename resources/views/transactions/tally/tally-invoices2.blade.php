@extends('app-layouts.admin-master')

@section('title', 'Invoices')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Invoices @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Tally @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('tally.invoices') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <input type="date" name="invoice_date" id="invoice_date" value="{{$date}}" class="my-control ml-2">
                                        <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>
                                    </div>
                                </div>
                            </div>
                        </form><!--end form-->
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th data-priority="6" class="text-center">S.No</th>
                                        <th data-priority="4" class="text-center">Invoice Date</th>
                                        <th data-priority="3">Route</th>                                        
                                        <th data-priority="2">Customer</th>
                                        <th data-priority="1" class="text-center">Invoice Number</th>
                                        <th data-priority="5">Sync</th>
                                        <th data-priority="6">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ getIndiaDate($invoice->invoice_date) }}</td>
                                            <td>{{ $invoice->route_name }}</td>
                                            <td>{{ $invoice->customer_name }}</td>
                                            <td class="text-center">{{ $invoice->invoice_num }}</td>                                                                                        
                                            <td class="text-center">
                                                <a href="#" class="sync" data-invoice="{{$invoice->invoice_num}}"><i class="dripicons-media-play text-primary font-20"></i></a>
                                            </td>
                                            <td id="status{{$invoice->invoice_num}}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> 
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            
            doInit();

            function doInit() {
                restrictMaxToTomorrow('#date');

                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "pageLength": 25,
                } );
            }

            $('body').on('click', '.sync', function (event) {
                let invoiceNum = $(this).attr('data-invoice');
                $.ajax({
                    url: "{{ route('tally.invoice') }}",
                    type: "GET",
                    data: { invoice_num: invoiceNum },
                    dataType: "xml", // Expect XML response
                    success: function(response) {
                        var xmlData = response; // Store XML response in variable
                        console.log(xmlData); // Check XML structure in console

                        // Convert XML Document to String
                        var xmlString = new XMLSerializer().serializeToString(xmlData);
                        console.log("XML Data Prepared:", JSON.stringify({ xml: xmlString })); // Debugging

                        // Send XML to Flask instead of Tally
                        $.ajax({
                            url: "http://127.0.0.1:5000/sync-with-tally",  // Flask API URL
                            type: "POST",
                            data: JSON.stringify({ xml: xmlString }),  // Send XML as JSON
                            contentType: "application/json", // Set content type to JSON
                            success: function(response) {
                                console.log("Flask Response:", response);
                                parseResponse(response, invoiceNum);
                            },
                            error: function(xhr, status, error) {
                                console.error("Error Sending to Flask:", error);
                                alert("Failed to send data");
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching XML:", error);
                    }
                });
            });

            function parseResponse(response, invoiceNum) {
                if (response.tally_response) {
                    // Parse XML response
                    var parser = new DOMParser();
                    var xmlDoc = parser.parseFromString(response.tally_response, "text/xml");
                    
                    // Get the <LINEERROR> element
                    var lineError = xmlDoc.getElementsByTagName("LINEERROR")[0];

                    if (lineError) {
                        let e = lineError.textContent.trim();
                        alert("Tally Error: " + e);
                        $('#status' + invoiceNum).text(e);
                    } else {
                        alert("Data sent successfully to Tally!");
                        $('#status' + invoiceNum).text("synced");
                    }
                }
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop