@extends('app-layouts.admin-master')

@section('title', 'Sync Masters')

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
                    @slot('title') Sync Masters @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Tally @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th class="text-center">S.No</th>
                                        <th class="pl-2">Master</th>
                                        <th class="pl-2">Description</th>
                                        <th class="text-center">Sync</th>
                                        <th class="pl-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($table as $record)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="pl-2">{{ $record['master'] }}</td>
                                            <td class="pl-2">{{ $record['description'] }}</td>
                                            <td class="text-center">
                                                <a href="#" class="sync" data-master="{{ $record['master'] }}" data-id="{{ $record['id'] }}"><i class="dripicons-media-play text-primary font-20"></i></a>
                                            </td>
                                            <td class="pl-2" id="status{{$record['master'] . $record['id']}}">{!! getTallyStatusWithBadge($record['sync_status']) !!}</td>                                            
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
            
            let master;
            let id;
            doInit();

            function doInit() {
                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "pageLength": -1,
                } );
            }

            $('body').on('click', '.sync', function (event) {
                // Stop event propagation to prevent automatic focus
                event.preventDefault(); // Prevents default focus behavior
                event.stopPropagation(); // Stops event bubbling

                master = $(this).attr('data-master');
                id = $(this).attr('data-id');                
                syncToTally();
            });

            function syncToTally() {                
                $.ajax({
                    url: "{{ route('tally.master') }}",
                    type: "GET",
                    data: { 
                        master : master,
                        id : id,
                    },
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
                                parseResponse(response);
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

            function parseResponse(response) {
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
                        Swal.fire('Tally Error', error, 'error');
                    } 
                    else {                        
                        // Swal.fire('Success',"Data successfully sent to Tally!",'success');
                        syncStatus = "Synced";
                    }

                    updateSyncStatus(syncStatus);
                }
            }

            function updateSyncStatus(syncStatus) {
                $.ajax({
                    url: "{{ route('tally.sync.master') }}", 
                    type: "GET",
                    data: {
                        master : master,
                        id : id,
                        sync_status : syncStatus,
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#status' + master + id).html(getTallyStatusWithBadge(syncStatus));
                        if(syncStatus == "Synced")
                            Swal.fire('Success', "Syncing Done", 'success');
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