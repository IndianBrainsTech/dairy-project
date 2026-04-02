@extends('app-layouts.admin-master')

@section('title', 'Tally Sync')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Tally Sync @endslot
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
                        <button type="button" id="btnSync" class="btn btn-gradient-pink waves-effect waves-light btn-sm ml-2">
                            <i class="mdi mdi-star mr-2"></i>Sync
                        </button>
                    </div><!--end card-body-->
                </div><!--end card--> 
            </div> <!--end col-->
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

            $('#btnSync').click(function () {
                // laravelToTally();
                // laravelToTallyViaFlask();
                syncLaravelAndTally();
            });

            function laravelToTally() {
                $.ajax({
                    url: "{{ route('tally.automate') }}",
                    type: "GET",
                    dataType: "xml", // Expect XML response
                    success: function(response) {
                        var xmlData = response; // Store XML response in variable
                        console.log(xmlData); // Check XML structure in console

                        // Convert XML Document to String
                        var xmlString = new XMLSerializer().serializeToString(xmlData);
                        console.log("XML Data Prepared:", xmlString); // Debugging

                        // Send XML to Tally
                        $.ajax({
                            url: "http://localhost:9000",  // Tally XML API URL
                            type: "POST",
                            data: xmlString,  // Sending XML data
                            contentType: "application/xml", // Important: Set content type to XML
                            processData: false, // Prevent jQuery from converting data to query string
                            success: function(response) {
                                console.log("Tally Response:", response);
                                alert("Data sent successfully to Tally!");
                            },
                            error: function(xhr, status, error) {
                                console.error("Error Sending to Tally:", error);
                                alert("Failed to send data to Tally.");
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching XML:", error);
                    }
                });
            }

            function laravelToTallyViaFlask() {
                $.ajax({
                    url: "{{ route('tally.automate') }}",
                    type: "GET",
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
                                alert("Data sent successfully to Flask!");
                            },
                            error: function(xhr, status, error) {
                                console.error("Error Sending to Flask:", error);
                                alert("Failed to send data to Flask.");
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching XML:", error);
                    }
                });
            }

            function syncLaravelAndTally() {
                $.ajax({
                    // url: "{{ route('tally.automate') }}",
                    url: "{{ route('tally.stock-items') }}",
                    type: "GET",
                    dataType: "xml", // Expect XML response
                    success: function(response) {
                        var xmlData = response; // Store XML response in variable
                        console.log(xmlData); // Check XML structure in console

                        // Convert XML Document to String
                        var xmlString = new XMLSerializer().serializeToString(xmlData);
                        console.log("XML Data Prepared:", JSON.stringify({ xml: xmlString })); // Debugging

                        sendToFlask(xmlString);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching XML:", error);
                    }
                });
            }

            function sendToFlask(xmlString) {
                $.ajax({
                    url: "http://127.0.0.1:5000/sync-with-tally",  // Flask API URL
                    type: "POST",
                    data: JSON.stringify({ xml: xmlString }),  // Send XML as JSON
                    contentType: "application/json", // Set content type to JSON
                    success: function(response) {
                        console.log("Flask Response:", response);
                        sendToLaravel(response.tally_response);
                        alert("Communication Successful!");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error Sending to Flask:", error);
                        alert("Failed to send data to Flask.");
                    }
                });
            }

            function sendToLaravel(tallyResponse) {
                $.ajax({
                    url: "{{ route('tally.save') }}",
                    type: "POST",
                    data: JSON.stringify({ tally_response: tallyResponse }),
                    contentType: "application/json",
                    success: function(response) {
                        console.log("Response saved in Laravel:", response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error saving response in Laravel:", error);
                        alert("Failed to save Tally response");
                    }
                });
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop