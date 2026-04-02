@extends('app-layouts.admin-master')

@section('title', 'View Diesel Bill Entry')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        hr {
            margin-top: 8px;
            margin-bottom: 8px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Diesel Bill Entry @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Entry @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-9 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <div class="px-2">
                            <div class="row my-2 position-relative">
                                <div class="col-md-3">Document Number</div>
                                <div class="col-md-9 app-bold position-relative">
                                    {{ $bill->document_number ?? 'NIL' }}

                                    <!-- Buttons on top-right inside col-md-9 -->
                                    <div class="position-absolute" style="top: 0; right: 0;">
                                        <button type="button" id="btn-prev" class="btn btn-info btn-sm px-3 py-1 mr-2"
                                                data-toggle="tooltip" data-placement="top" title="Left Arrow (&lt;)">
                                            Prev
                                        </button>
                                        <button type="button" id="btn-next" class="btn btn-secondary px-3 btn-sm py-1"
                                                data-toggle="tooltip" data-placement="top" title="Right Arrow (&gt;)">
                                            Next
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Document Date</div>
                                <div class="col-md-9 app-bold">{{ $bill->document_date }}</div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-3">Petrol Bunk</div>
                                <div class="col-md-9 app-bold">{{ $bill->bunk_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Bill Number</div>
                                <div class="col-md-9 app-bold">{{ $bill->bill_number }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Bill Date</div>
                                <div class="col-md-9 app-bold">{{ $bill->bill_date }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Status</div>
                                <div class="col-md-9 app-bold">{{ $bill->status->label() }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Created by</div>
                                <div class="col-md-9"><span class="app-bold">{{ $bill->createdBy->name }}</span> at {{ displayDateTimeIST($bill->created_at) }}</div>
                            </div>
                            @if($bill->updated_by)
                                <div class="row my-2">
                                    <div class="col-md-3">Edited by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $bill->updatedBy->name }}</span>@if(!$bill->actioned_by) at {{ displayDateTimeIST($bill->updated_at) }}@endif</div>
                                </div>
                            @endif
                            @if($bill->actioned_by)
                                <div class="row my-2">
                                    <div class="col-md-3">{{ $bill->status->label() }} by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $bill->actionedBy->name }}</span> at {{ displayDateTimeIST($bill->actioned_at) }}</div>
                                </div>
                            @endif
                            <hr/>

                            <div class="row my-2">
                                <div class="col-md-3">Vehicle Number</div>
                                <div class="col-md-9 app-bold">{{ $bill->vehicle_number }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Driver Name</div>
                                <div class="col-md-9 app-bold">{{ $bill->driver_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Route</div>
                                <div class="col-md-9 app-bold">{{ $bill->route_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Fuel (Ltrs)</div>
                                <div class="col-md-9 app-bold">{{ $bill->fuel }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Rate</div>
                                <div class="col-md-9 app-bold">{{ $bill->rate }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount</div>
                                <div class="col-md-9 app-bold">{{ $bill->amount }}</div>
                            </div>
                            <hr/>

                            <div class="row my-2">
                                <div class="col-md-3">Opening KM</div>
                                <div class="col-md-9 app-bold">{{ $bill->opening_km }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Closing KM</div>
                                <div class="col-md-9 app-bold">{{ $bill->closing_km }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Running KM</div>
                                <div class="col-md-9 app-bold">{{ $bill->running_km }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">KM per Liter</div>
                                <div class="col-md-9 app-bold">{{ $bill->kmpl }}</div>
                            </div>
                            <hr/>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
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

            let id = String(@json($bill->id));
            const idList = @json($id_list).split(',');

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();

                $('#btn-prev').on("click", showPreviousRecord);
                $('#btn-next').on("click", showNextRecord);

                $(document).on('keydown', function(event) {
                    if (event.key === 'ArrowLeft')
                        showPreviousRecord();
                    else if (event.key === 'ArrowRight')
                        showNextRecord();
                });
            }

            function showPreviousRecord() {
                let index = idList.indexOf(id);
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Diesel Bill!','warning');
                }
                else {
                    id = idList[index - 1];
                    showDocument();
                }
            }

            function showNextRecord() {
                let index = idList.indexOf(id);
                if(index == idList.length-1) {
                    Swal.fire('Sorry!','No Next Diesel Bill!','warning');
                }
                else {
                    id = idList[index + 1];
                    showDocument();
                }
            }

            function showDocument() {
                // Create a form element
                const form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('diesel-bills.entries.show') }}"
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
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop