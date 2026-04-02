@extends('app-layouts.admin-master')

@section('title', 'View Diesel Bill Statement')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        hr {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .div-cancelled {
            position: relative;
            overflow: hidden; /* Ensures watermark stays within the div */
        }

        .div-cancelled::before {
            content: "×"; /* Unicode multiplication symbol */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(0deg);
            font-size: 600px;
            color: rgba(200, 0, 0, 0.1); /* red with transparency */
            pointer-events: none; /* allow clicking through */
            user-select: none;
        }

        .div-cancelled::after {
            content: "Cancelled";
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: rgba(255, 0, 0, 0.25);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
            filter: blur(0.4px);
        }

        @media print {
            @page {
                size: A4;
                margin: 0.4in;
            }

            .app-table th {
                font-size: 13pt !important;
            }

            .app-table td {
                font-size: 12pt !important;
            }

            /* Driver and Route Columns */
            #tbl-statement th:nth-child(4),
            #tbl-statement td:nth-child(4),
            #tbl-statement th:nth-child(5),
            #tbl-statement td:nth-child(5) {
                width: 120px !important;
                max-width: 120px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                transition: font-size 0.1s;
            }
        }        
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Diesel Bill Statement @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Generation @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="px-2">
                            <div class="row my-2 position-relative">
                                <div class="col-md-3">Document Number</div>
                                <div class="col-md-9 app-bold position-relative">
                                    {{ $record->document_number ?? 'NIL' }}

                                    <!-- Buttons on top-right -->
                                    <div class="position-absolute" style="top: 0; right: 0;">
                                        @if($record->status === \App\Enums\Status::ACCEPTED)
                                            <button type="button" id="btn-pdf" class="btn btn-pink btn-sm px-2 py-1 mr-2" 
                                                    data-toggle="tooltip" data-placement="top" title="PDF">
                                                &nbsp;<i class="far fa-file-pdf"></i>&nbsp;
                                            </button>
                                            <button type="button" id="btn-print" class="btn btn-pink btn-sm px-2 py-1 mr-3" 
                                                    data-toggle="tooltip" data-placement="top" title="Print">
                                                &nbsp;<i class="fa fa-print"></i>&nbsp;
                                            </button>
                                        @endif
                                        <button type="button" id="btn-prev" class="btn btn-info btn-sm px-3 py-1 mr-2"
                                                data-toggle="tooltip" data-placement="top" title="Left Arrow (&lt;)">
                                            Prev
                                        </button>
                                        <button type="button" id="btn-next" class="btn btn-secondary btn-sm px-3 py-1 mr-3"
                                                data-toggle="tooltip" data-placement="top" title="Right Arrow (&gt;)">
                                            Next
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Document Date</div>
                                <div class="col-md-9 app-bold">{{ $record->document_date }}</div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Created by</div>
                                <div class="col-md-9"><span class="app-bold">{{ $record->createdBy->name }}</span> at {{ displayDateTimeIST($record->created_at) }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Status</div>
                                <div class="col-md-9 app-bold">{{ $record->status->label() }}</div>
                            </div>
                            @if($record->actioned_by)
                                <div class="row my-2">
                                    <div class="col-md-3">{{ $record->status->label() }} by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $record->actionedBy->name }}</span> at {{ displayDateTimeIST($record->actioned_at) }}</div>
                                </div>
                            @endif
                            <hr/>

                            <div id="div-statement" class="table-responsive dash-social px-2 @if($record->status === \App\Enums\Status::CANCELLED) div-cancelled @endif">
                                @include('app-partials.print-header', ['title' => 'DIESEL BILL STATEMENT'])
                                <h2 id="hdg-bunk" class="app-h2 pt-2">{{ $record->bunk_name }}</h2>
                                <h3 id="hdg-duration" class="app-h3 pb-2">{{ $record->getPeriod() }}</h3>
                                <table id="tbl-statement" class="app-table text-nowrap">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Date</th>
                                            <th class="text-left pl-2">Vehicle</th>
                                            <th class="text-left pl-2">Driver</th>
                                            <th class="text-left pl-2">Route</th>
                                            <th class="text-right pr-2">Fuel</th>
                                            <th class="text-right pr-2">Pre KM</th>
                                            <th class="text-right pr-2">Cur KM</th>
                                            <th class="text-right pr-2">Run KM</th>
                                            <th class="text-right pr-2">KMPL</th>
                                            <th class="text-right pr-2">Rate</th>
                                            <th class="text-right pr-2">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($record->diesel_bills as $bill)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">{{ $bill->bill_date }}</td>
                                                <td class="text-left pl-2">{{ $bill->vehicle_number}}</td>
                                                <td class="text-left pl-2">{{ $bill->driver_name}}</td>
                                                <td class="text-left pl-2">{{ $bill->route_name}}</td>
                                                <td class="text-right pr-2">{{ $bill->fuel}}</td>
                                                <td class="text-right pr-2">{{ $bill->opening_km}}</td>
                                                <td class="text-right pr-2">{{ $bill->closing_km}}</td>
                                                <td class="text-right pr-2">{{ $bill->running_km}}</td>
                                                <td class="text-right pr-2">{{ $bill->kmpl}}</td>
                                                <td class="text-right pr-2">{{ $bill->rate}}</td>
                                                <td class="text-right pr-2">{{ $bill->amount}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="text-right">
                                        <tr class="thead-light">
                                            <th colspan="5" class="text-center">Total / Average</th>
                                            <th>{{ $record->total_fuel }}</th>
                                            <th>{{ $record->diesel_bills->sum('opening_km') }}</th>
                                            <th>{{ $record->diesel_bills->sum('closing_km') }}</th>
                                            <th>{{ (int) $record->total_running_km }}</th>
                                            <th>{{ $record->average_kmpl }}</th>
                                            <th>{{ $record->average_rate }}</th>
                                            <th>{{ $record->total_amount }}</th>
                                        </tr>
                                        @if((float) $record->tds_amount)
                                            <tr>
                                                <th colspan="11">TDS</th>
                                                <th>{{ $record->tds_amount }}</th>
                                            </tr>
                                        @endif
                                        <tr>
                                            <th colspan="11">Round Off</th>
                                            <th>{{ getRoundOffWithSign($record->round_off) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="11">Net Amount</th>
                                            <th>{{ getTwoDigitPrecision($record->net_amount) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let id = String(@json($record->id));
            const idList = @json($id_list).split(',');
            doInit();

            function doInit() {                
                $("body").toggleClass("enlarge-menu");

                $(document).on('keydown', function(event) {
                    if (event.key === 'ArrowLeft')
                        showPreviousRecord();
                    else if (event.key === 'ArrowRight')
                        showNextRecord();
                });

                $('#btn-prev').on("click", showPreviousRecord);
                $('#btn-next').on("click", showNextRecord);
                $('#btn-print').on("click", printDocument);
                $('#btn-pdf').on("click", downloadPdf);
            }

            function showPreviousRecord() {
                let index = idList.indexOf(id);
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Diesel Bill Statement!','warning');
                }
                else {
                    id = idList[index - 1];
                    showDocument();
                }
            }

            function showNextRecord() {
                let index = idList.indexOf(id);
                if(index == idList.length-1) {
                    Swal.fire('Sorry!','No Next Diesel Bill Statement!','warning');
                }
                else {
                    id = idList[index + 1];
                    showDocument();
                }
            }

            function printDocument() {
                let originalContents = $('body').html();
                adjustColumnFonts();
                let printContents = $('#div-statement').html();
                $('body').html(printContents);                
                window.print();
                $('body').html(originalContents);
            }

            function showDocument() {
                // Create a form element
                const form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('diesel-bills.statements.show') }}"
                });

                // Add CSRF token
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'id', 'value': id }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'id_list', 'value': idList }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            }

            function adjustColumnFonts() {
                $('#tbl-statement td:nth-child(4), #tbl-statement td:nth-child(5)').each(function () {

                    let cell = $(this);
                    let originalFont = cell.css('font-size');

                    // Try to reduce font until it fits
                    for (let size = 12; size >= 8; size--) {
                        cell.css('font-size', size + 'pt');
                        if (this.scrollWidth <= this.clientWidth) return;
                    }

                    // If still overflow, final clipping remains
                });
            }

            function downloadPdf() {
    $.ajax({
        url: '{{ route("statement.pdf") }}',
        method: 'GET',
        xhrFields: {
            responseType: 'blob' // handle binary response
        },
        success: function (data, status, xhr) {
            console.log('AJAX Status (downloadXml):', status);
            console.log('Response Headers:', xhr.getAllResponseHeaders());
            console.log('HTTP Status Code:', xhr.status);

            let filename = "download.pdf";
            const disposition = xhr.getResponseHeader('Content-Disposition');

            if (disposition && disposition.indexOf('filename=') !== -1) {
                filename = disposition.split('filename=')[1].split(';')[0].replace(/['"]/g, '');
            }

            const blob = new Blob([data]);
            const downloadUrl = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();

            window.URL.revokeObjectURL(downloadUrl);
        },
        error: function (xhr, status, error) {            
            console.error('AJAX Error (downloadExcel):', {
                status: status,
                error: error,                
                response: xhr.responseText,
            });
            Swal.fire('Sorry!','Download failed!','error');            
        }
    });
}

            // function downloadPdf() {                
            //     let divContent = document.getElementById('div-statement').innerHTML;

            //     fetch('{{ route("statement.pdf") }}', {
            //         method: 'POST',
            //         headers: {
            //             'Content-Type': 'application/json',
            //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //         },
            //         body: JSON.stringify({ html: divContent })
            //     })
            //     .then(response => response.blob())
            //     .then(blob => {
            //         let url = window.URL.createObjectURL(blob);
            //         let a = document.createElement('a');
            //         a.href = url;
            //         a.download = 'statement.pdf';
            //         document.body.appendChild(a);
            //         a.click();
            //         a.remove();
            //     })
            //     .catch(error => console.error('Error generating PDF:', error));
            // }

            // function downloadPdf() {
            //     const element = document.getElementById('div-statement');
                
            //     const options = {
            //         margin:       0.5,
            //         filename:     'statement.pdf',
            //         image:        { type: 'jpeg', quality: 0.98 },
            //         html2canvas:  { scale: 2 },
            //         jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            //     };

            //     html2pdf().set(options).from(element).save();
            // }

//             function downloadPdf() {
//     const element = document.getElementById('div-statement').cloneNode(true);
//     document.body.innerHTML = '';
//     document.body.appendChild(element);

//     const opt = {
//         margin: 0,
//         filename: 'statement.pdf',
//         html2canvas: { scale: 2, scrollY: 0 },
//         jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait' }
//     };

//     html2pdf().set(opt).from(element).save().then(() => {
//         window.location.reload(); // restore page after PDF save
//     });
// }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop