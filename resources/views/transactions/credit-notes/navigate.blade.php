@extends('app-layouts.admin-master')

@section('title', 'View Credit Note')

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
                    @slot('title') View Credit Note @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Credit Notes @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Document Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    <div>Document Number</div>
                                    <div class="mt-2">Document Date</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="app-bold blue-text">{{ $record->document_number }}</div>
                                    <div class="app-bold mt-2">{{ $record->document_date_for_display }}</div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="button" id="btn-prev" class="btn btn-info py-1 mr-2" 
                                        data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">
                                        Prev
                                    </button>
                                    <button type="button" id="btn-next" class="btn btn-secondary py-1" 
                                        data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">
                                        Next
                                    </button>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Customer</div>
                                <div class="col-md-9 app-bold">{{ $record->customer->customer_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Narration</div>
                                <div class="col-md-9 app-bold">{{ $record->narration }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Reason</div>
                                <div class="col-md-9 app-bold">{{ $record->reason->label() }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount</div>
                                <div class="col-md-9 app-bold">{{ (float) $record->amount }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Status</div>
                                <div class="col-md-9 app-bold">{{ $record->status->label() }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Created by</div>
                                <div class="col-md-9"><span class="app-bold">{{ $record->creator->name }}</span> at {{ displayDateTimeIST($record->created_at) }}</div>
                            </div>
                            @if($record->updated_by)
                                <div class="row my-2">
                                    <div class="col-md-3">Updated by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $record->updater->name }}</span> at {{ displayDateTimeIST($record->updated_at) }}</div>
                                </div>
                            @endif
                            @if($record->actioned_by)
                                <div class="row my-2">
                                    <div class="col-md-3">{{ $record->status->label() }} by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $record->actioner->name }}</span> at {{ displayDateTimeIST($record->actioned_at) }}</div>
                                </div>
                                @if($record->status === \App\Enums\DocumentStatus::CANCELLED)
                                    <div class="row my-2">
                                        <div class="col-md-3">Cancel Remarks</div>
                                        <div class="col-md-9">{{ '' }}</div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Items Table -->
                        <h6 class="app-heading p-2 pt-2 mb-1">Details :</h6>
                        <div class="table-responsive dash-social px-2">
                            <table class="table table-bordered table-sm mb-2">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Invoice</th>
                                        <th class="text-right pr-2">Amount</th>
                                        <th class="text-right pr-2">Paid</th>
                                        <th class="text-right pr-2">Outstanding</th>
                                        <th class="text-right pr-2">Adjusted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($record->items as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $item->invoice_date }}</td>
                                            <td class="text-center">{{ $item->invoice_number }}</td>
                                            <td class="text-right pr-2">{{ (float) $item->invoice_amount }}</td>
                                            <td class="text-right pr-2">{{ $item->paid_amount ? (float) $item->paid_amount : "" }}</td>
                                            <td class="text-right pr-2">{{ (float) $item->outstanding_amount }}</td>
                                            <td class="text-right pr-2">{{ (float) $item->adjusted_amount }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="5"></th>
                                        <th class="text-right pr-2">Total</th>
                                        <th class="text-right pr-2">{{ (float) $record->amount }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($record->status === \App\Enums\DocumentStatus::DRAFT)
                            <hr/>
                            <div class="text-center">
                                @can('update_credit_note')
                                    <button type="button" class="btn btn-dark px-4 py-1 mr-3" id="btn-edit">
                                        Edit
                                    </button>
                                @endcan

                                @can('cancel_credit_note')
                                    <button type="button" class="btn btn-danger px-3 py-1 ml-3" 
                                        data-toggle="modal" data-animation="bounce" data-target="#mdl-cancel">
                                        Cancel
                                    </button>
                                @endcan
                            </div>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>

    @can('cancel_credit_note')
        @include('app-partials.modal-cancel', ['modalFor' => 'Credit Note'])
    @endcan
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper-v1.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let currentDocument = @json($record->document_number);
            const documentList = @json($document_list).split(',');

            setMenuItemActive('Transactions','ul-credit-notes','li-credit-notes-list');

            $(document).on('keydown', function(event) {
                if (event.key === 'ArrowLeft') {
                    $('#btn-prev').click();
                }
                else if (event.key === 'ArrowRight') {
                    $('#btn-next').click();
                }
            });

            $('#btn-prev').on("click", function () {
                let index = documentList.indexOf(currentDocument);
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Credit Note!','warning');
                }
                else {
                    currentDocument = documentList[index - 1];
                    showDocument();
                }
            });

            $('#btn-next').on("click", function () {
                let index = documentList.indexOf(currentDocument);
                if(index == documentList.length-1) {
                    Swal.fire('Sorry!','No Next Credit Note!','warning');
                }
                else {
                    currentDocument = documentList[index + 1];
                    showDocument();
                }
            });

            
            $('#btn-edit').on("click", function () {
                const url = "{{ route('credit-notes.edit', $record->id) }}";
                window.location.href = url;
            });
                        
            $('#btn-cancel').on("click", function () {
                let remarks = $("#remarks").val();
                if(!remarks) {
                    Swal.fire('Sorry','Please enter reason for credit note cancel!','warning');
                    return;
                }
                else {
                    $('#btn-cancel').prop('disabled', true);                                                
                    $.ajax({
                        url: "{{ route('credit-notes.cancel', $record->id) }}",
                        type: 'PATCH',
                        data: { remarks : remarks },
                        dataType: 'json'
                    })
                    .done(response => {
                        console.log("AJAX Success:", response);
                        if(response.success)
                            Swal.fire('Success!', response.message, 'success')
                                .then(() => window.location.href = "{{ route('credit-notes.index') }}");
                        else
                            Swal.fire('Sorry!', response.message, 'error');
                    })
                    .fail((xhr, status, error) => {
                        handleAjaxError(xhr, status, error);
                    })
                    .always(() => {
                        $('#btn-cancel').prop('disabled', false);
                    });
                }
            });            

            function showDocument() {
                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('credit-notes.navigate') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'current_document', 'value': currentDocument }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'document_list', 'value': documentList }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop