@extends('app-layouts.admin-master')

@section('title', 'Sync Invoices')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
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
                    @slot('title') Sync Invoices @endslot
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
                                        <input type="date" name="invoice_date" value="{{$date}}" class="my-control ml-2">
                                        <select name="invoice_type" class="my-control mx-2">
                                            <option value="Pouch" @selected($type=="Pouch")>Pouch Invoices</option>
                                            <option value="Tax" @selected($type=="Tax")>Tax Invoices</option>
                                            <option value="BulkMilk" @selected($type=="BulkMilk")>Bulk Milk Invoices</option>
                                        </select>
                                        <div class="btn-group btn-group-toggle mx-2" data-toggle="buttons">
                                            <label class="btn btn-outline-warning">
                                                <input type="radio" name="sync_type" value="All" id="rdoAll" @checked($sync_type=="All")>&ensp;All&ensp;
                                            </label>
                                            <label class="btn btn-outline-warning">
                                                <input type="radio" name="sync_type" value="Unsynced" id="rdoUnsynced" @checked($sync_type=="Unsynced")>Unsynced
                                            </label>
                                        </div>
                                        <input type="submit" value="Load" class="btn btn-primary btn-sm mx-3 px-4"/>
                                        <button type="button" id="syncAll" class="btn btn-gradient-pink mx-2 px-3"><i class="mdi mdi-sync mr-2"></i>Sync</button>                                        
                                    </div>
                                </div>
                            </div>
                        </form><!--end form-->
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th class="text-center">S.No</th>
                                        <th class="pl-2">Route</th>
                                        <th class="pl-2">Customer</th>
                                        <th class="text-center">Invoice Number</th>
                                        <th class="text-center d-none">Sync</th>
                                        <th class="pl-2">Tally Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="pl-2">{{ $invoice->route_name }}</td>
                                            <td class="pl-2">{{ $invoice->customer_name }}</td>
                                            <td class="text-center">{{ $invoice->invoice_num }}</td>
                                            <td class="text-center">
                                                <a href="#" class="sync" data-invoice="{{$invoice->invoice_num}}"><i class="dripicons-media-play text-primary font-20"></i></a>
                                            </td>
                                            <td class="pl-2" id="status{{$invoice->invoice_num}}">{!! getTallyStatusWithBadge($invoice->tally_sync) !!}</td>                                            
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
            
            let invoices;
            let isSyncAll;
            let i;            
            doInit();

            function doInit() {
                restrictMaxToTomorrow('#date');

                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "pageLength": -1,
                } );
            }

            $('#syncAll').on('click', function () {
                i = 0;
                isSyncAll = true;
                let table = $('#datatable').DataTable();
                invoices = table.column(3,{search:'applied'}).data().toArray();                
                syncToTally(invoices[i]);
                // invoices.forEach(syncToTally);
            });

            $('body').on('click', '.sync', function (event) {
                // Stop event propagation to prevent automatic focus
                event.preventDefault(); // Prevents default focus behavior
                event.stopPropagation(); // Stops event bubbling

                isSyncAll = false;
                let invoiceNum = $(this).attr('data-invoice');
                syncToTally(invoiceNum);
            });

            function syncToTally(invoiceNum) {
                $.ajax({
                    url: "{{ route('tally.invoice') }}",
                    type: "GET",
                    data: { invoice_num: invoiceNum },
                    dataType: "xml", // Expect XML response
                    success: function(response) {
                        let xmlData = response; // Store XML response in variable
                        console.log(xmlData); // Check XML structure in console

                        // Convert XML Document to String
                        let xmlString = new XMLSerializer().serializeToString(xmlData);
                        // console.log("XML Data Prepared:", JSON.stringify({ xml: xmlString })); // Debugging

                        // Send XML to Flask
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
                                Swal.fire('Sorry!','Failed to send data!','warning');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching XML:", error);
                    }
                });
            }

            function parseResponse(response, invoiceNum) {
                if (response.tally_response) {
                    // Parse XML response
                    let parser = new DOMParser();
                    let xmlDoc = parser.parseFromString(response.tally_response, "text/xml");
                    
                    // Get the <LINEERROR> element
                    let lineError = xmlDoc.getElementsByTagName("LINEERROR")[0];
                    let syncStatus;

                    if (lineError) {
                        let error = lineError.textContent.trim();
                        syncStatus = error;
                        Swal.fire('Tally Error', "Invoice Number : " + invoiceNum + "<br/>" + error, 'error');
                    } 
                    else {                        
                        // Swal.fire('Success',"Data successfully sent to Tally!",'success');
                        syncStatus = "Synced";
                    }

                    updateSyncStatus(invoiceNum, syncStatus);
                }
            }

            function updateSyncStatus(invoiceNum, syncStatus) {
                $.ajax({
                    url: "{{ route('tally.sync.invoice') }}",
                    type: "GET",
                    data: { 
                        invoice_num : invoiceNum,
                        sync_status : syncStatus,
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#status' + invoiceNum).html(getTallyStatusWithBadge(syncStatus));
                        console.log(invoiceNum + " : " + response.message);
                        if(isSyncAll && syncStatus == "Synced") {                            
                            i++;
                            if(i < invoices.length)
                                syncToTally(invoices[i]);
                            else if(i == invoices.length)
                                Swal.fire('Success', "Syncing Done", 'success');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error Updating Sync Status : " + invoiceNum + " ", error);
                    }
                });
            }

            function getTallyStatusWithBadge(status) {
                if (!status)
                    return null;
                else if (status === "Synced")
                    return `<span class="badge badge-md badge-soft-success">Synced</span>`;
                else
                    return `<span class="badge badge-md badge-soft-danger">${status}</span>`;
            }

        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop