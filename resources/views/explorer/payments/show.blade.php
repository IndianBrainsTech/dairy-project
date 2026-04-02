@extends('app-layouts.admin-master')

@section('title', 'View Bank Payment')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Bank Payment @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Bank Payments @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="px-2 mb-3">
                            <div class="row my-2 position-relative">
                                <div class="col-md-3">Document Number</div>
                                <div class="col-md-9 app-bold position-relative">
                                    {{ $document->document_number }}

                                    <!-- Buttons on top-right inside col-md-9 -->
                                    <div class="position-absolute" style="top: 0; right: 0;">
                                        <button type="button" id="btn-excel" class="btn btn-pink py-0 px-2 mr-3" 
                                                data-toggle="tooltip" data-placement="top" title="Excel (Ctrl+E)">
                                                <i class="mdi mdi-file-excel font-18"></i>
                                        </button>
                                        <button type="button" id="btn-prev" class="btn btn-info btn-sm px-3 py-1 mr-2"
                                                data-toggle="tooltip" data-placement="top" title="Left Arrow (&lt;)">
                                            Prev
                                        </button>
                                        <button type="button" id="btn-next" class="btn btn-secondary px-3 btn-sm py-1 mr-2"
                                                data-toggle="tooltip" data-placement="top" title="Right Arrow (&gt;)">
                                            Next
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Date</div>
                                <div class="col-md-9 app-bold">{{ $document->payment_date }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Transaction Type</div>
                                <div class="col-md-9 app-bold">{{ $document->payment_type->label() }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Bank</div>
                                <div class="col-md-9 app-bold">{{ $document->bank_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount</div>
                                <div class="col-md-9 app-bold">{{ $document->total_amount }}</div>
                            </div>
                        </div>

                        <table class="app-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th class="text-center">Document</th>
                                    <th class="text-center">Period</th>
                                    <th class="text-left pr-2">Name</th>
                                    <th class="text-right pr-2">Amount</th>
                                    <th class="text-center">Account Number</th>
                                    <th class="text-center">Bank</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $record['document_number'] }}</td>
                                        <td class="text-center">{{ $record['period'] }}</td>
                                        <td class="text-left pr-2">{{ $record['name'] }}</td>
                                        <td class="text-right pr-2">{{ $record['amount'] }}</td>
                                        <td class="text-center">{{ $record['account_number'] }}</td>
                                        <td class="text-center">{{ $record['bank_name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/file-helper.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const documentNumber = @json($document->document_number);
            const documentList = @json($document_list).split(',');
            doInit();

            function doInit() {
                $('a[href="#MenuExplorer"]').click();

                $('#btn-excel').on("click", downloadAsExcel);
                $('#btn-prev').on("click", showPreviousDocument);
                $('#btn-next').on("click", showNextDocument);

                $(document).on('keydown', function(event) {
                    if (event.ctrlKey && event.key.toUpperCase() === 'E') {
                        event.preventDefault();                        
                        downloadAsExcel();
                    }
                    if (event.key === 'ArrowLeft') {
                        showPreviousDocument();
                    }
                    else if (event.key === 'ArrowRight') {
                        showNextDocument();
                    }
                });
            }

            function downloadAsExcel() {
                const id = @json($document->id);
                const route = `{{ route('downloads.excel.bank.payment', ['id' => '__ID__']) }}`;
                const url = route.replace('__ID__', encodeURIComponent(id));
                downloadExcel(url);
            }

            function showPreviousDocument() {
                let index = documentList.indexOf(documentNumber);                
                if(index === 0)
                    Swal.fire('Sorry!','No Previous Document!','warning');
                else
                    showDocument(documentList[index - 1]);
            }

            function showNextDocument() {
                let index = documentList.indexOf(documentNumber);                
                if(index === documentList.length-1)
                    Swal.fire('Sorry!','No Next Document!','warning');
                else
                    showDocument(documentList[index + 1]);
            }

            function showDocument(document) {
                $('<form>', { method: 'POST', action: "{{ route('payments.show') }}" })
                    .append($('<input>', { type: 'hidden', name: '_token', value: $('meta[name="csrf-token"]').attr('content') }))
                    .append($('<input>', { type: 'hidden', name: 'document', value: document }))
                    .append($('<input>', { type: 'hidden', name: 'document_list', value: documentList }))
                    .appendTo('body')
                    .trigger('submit');
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop