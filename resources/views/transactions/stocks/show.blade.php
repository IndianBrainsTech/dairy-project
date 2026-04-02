@extends('app-layouts.admin-master')

@section('title', 'View Stock')

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
                    @slot('title') View Stock @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Stocks @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-9 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Stock Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    <div>Document Number</div>
                                    <div class="mt-2">Document Date</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="app-bold blue-text">{{ $stock->document_number }}</div>
                                    <div class="app-bold mt-2">{{ displayDate($stock->document_date) }}</div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="button" id="btn-prev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</button>
                                    <button type="button" id="btn-next" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</button>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Status</div>
                                <div class="col-md-9 app-bold">{{ $stock->status_label }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Created by</div>
                                <div class="col-md-9"><span class="app-bold">{{ $stock->createdBy->name }}</span> at {{ displayDateTimeIST($stock->created_at) }}</div>
                            </div>
                            @if($stock->updated_by)
                                <div class="row my-2">
                                    <div class="col-md-3">Updated by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $stock->updatedBy->name }}</span> at {{ displayDateTimeIST($stock->updated_at) }}</div>
                                </div>
                            @endif
                            @if($stock->actioned_by)
                                <div class="row my-2">
                                    <div class="col-md-3">{{ $stock->status_label }} by</div>
                                    <div class="col-md-9"><span class="app-bold">{{ $stock->actionedBy->name }}</span> at {{ displayDateTimeIST($stock->actioned_at) }}</div>
                                </div>
                                @if($stock->status === \App\Enums\Status::CANCELLED)
                                    <div class="row my-2">
                                        <div class="col-md-3">Cancel Remarks</div>
                                        <div class="col-md-9">{{ $stock->remarks }}</div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Stock Table -->
                        <h6 class="app-heading p-2 pt-2 mb-1">Stock Data :</h6>
                        <div class="table-responsive dash-social px-2">
                            <table class="table table-bordered table-sm mb-2">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-center">Item Name</th>
                                        <th class="text-right pr-2">Qty</th>
                                        <th class="text-left pl-2">Unit</th>
                                        <th class="text-center">Batch Number</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stock->items as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2">{{ $item->item_name }}</td>
                                            <td class="text-right pr-2">{{ (float)$item->quantity }}</td>
                                            <td class="text-left pl-2">{{ $item->unit->display_name }}</td>
                                            <td class="text-left pl-2">{{ $item->batch_number }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($stock_history)
                            <hr/>
                            <h6 class="app-heading p-2 mb-1">Stock History :</h6>
                            @php
                                $changeLabels = [
                                    'INSERT' => ['label' => 'Records Added', 'color' => 'success'],
                                    'DELETE' => ['label' => 'Records Deleted', 'color' => 'danger'],
                                    'UPDATE' => ['label' => 'Records Modified', 'color' => 'warning'],
                                ];
                            @endphp
                            @foreach ($stock_history as $history)
                                <h6 class="p-2 mt-0 mb-1"><span class="color-old-mauve">Version {{ $history['version'] }}:</span> {{ $history['title'] }}</h6>
                                @if(!empty($history['changes']))
                                    @foreach($changeLabels as $type => $info)
                                        @if(!empty($history['changes'][$type]))
                                            <div class="p-2 m-2 border rounded">
                                                <p class="mb-1 fw-bold text-{{ $info['color'] }}">
                                                    {{ $info['label'] }}
                                                </p>
                                                <ol class="mb-0 ps-3">
                                                    @foreach ($history['changes'][$type] as $item)
                                                        @if($type === 'UPDATE')
                                                            <li>
                                                                <span class="text-muted">From:</span>
                                                                {{ $item['before']['item_name'] }} - {{ $item['before']['qty'] }} {{ $item['before']['unit'] }} {{ !empty($item['before']['batch']) ? ' - ' . $item['before']['batch'] : '' }}<br>
                                                                <span class="text-muted mr-3">To:</span>
                                                                {{ $item['after']['item_name'] }} - {{ $item['after']['qty'] }} {{ $item['after']['unit'] }} {{ !empty($item['after']['batch']) ? ' - ' . $item['after']['batch'] : '' }}
                                                            </li>
                                                        @else
                                                            <li>
                                                                {{ $item['item_name'] }} - {{ $item['qty'] }} {{ $item['unit'] }} {{ !empty($item['batch']) ? ' - ' . $item['batch'] : '' }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ol>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                <div class="table-responsive dash-social px-2">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="text-center">Item Name</th>
                                                <th class="text-right pr-2">Qty</th>
                                                <th class="text-left pl-2">Unit</th>
                                                <th class="text-center">Batch Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($history['records'] as $record)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="text-left pl-2">{{ $record['item_name'] }}</td>
                                                    <td class="text-right pr-2">{{ $record['qty'] }}</td>
                                                    <td class="text-left pl-2">{{ $record['unit'] }}</td>
                                                    <td class="text-left pl-2">{{ $record['batch'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            @if($stock->actioned_by)
                                <h6 class="p-2 mt-0 mb-0">
                                    <span class="color-old-mauve">{{ $stock->status_label }} by</span>
                                    <span class="app-bold">{{ $stock->actionedBy->name }}</span> at {{ displayDateTimeIST($stock->actioned_at) }}
                                </h6>
                                @if($stock->status === \App\Enums\Status::CANCELLED)
                                    <p class="px-2 py-0 mb-0">Remarks: {{ $stock->remarks }}</p>
                                @endif
                            @endif
                        @endif

                        @if($stock->status === \App\Enums\Status::PENDING)
                            <hr/>
                            <div class="text-center">
                                @can('update_stock') <button type="button" class="btn btn-dark px-4 py-1 mr-3" id="btn-edit">Edit</button> @endcan
                                @can('cancel_stock') <button type="button" class="btn btn-danger px-3 py-1 ml-3" data-toggle="modal" data-animation="bounce" data-target="#mdl-cancel">Cancel</button> @endcan
                            </div>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>

    @can('cancel_stock')
        @include('app-partials.modal-cancel', ['modalFor' => 'Stock'])
    @endcan
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/script-helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let number = @json($stock->document_number);
            const numberList = @json($number_list).split(',');

            $(document).on('keydown', function(event) {
                if (event.key === 'ArrowLeft') {
                    $('#btn-prev').click();
                }
                else if (event.key === 'ArrowRight') {
                    $('#btn-next').click();
                }
            });

            $('#btn-prev').on("click", function () {
                let index = numberList.indexOf(number);
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Stock!','warning');
                }
                else {
                    number = numberList[index - 1];
                    showDocument();
                }
            });

            $('#btn-next').on("click", function () {
                let index = numberList.indexOf(number);
                if(index == numberList.length-1) {
                    Swal.fire('Sorry!','No Next Stock!','warning');
                }
                else {
                    number = numberList[index + 1];
                    showDocument();
                }
            });

            @can('update_stock')
                $('#btn-edit').on("click", function () {
                    const stockId = @json($stock->id);
                    const url = "{{ route('stocks.edit', ['stock' => '__ID__']) }}".replace('__ID__', stockId);
                    window.location.href = url;
                });
            @endcan

            @can('cancel_stock')
                $('#btn-cancel').on("click", function () {
                    let remarks = $("#remarks").val();
                    if(!remarks) {
                        Swal.fire('Sorry','Please enter reason for stock cancel!','warning');
                        return;
                    }
                    else {
                        $('#btn-cancel').prop('disabled', true);
                        const stockId = @json($stock->id);
                        const url = "{{ route('stocks.cancel', ['stock' => '__ID__']) }}".replace('__ID__', stockId);
                        $.ajax({
                            url: url,
                            type: 'PUT',
                            data: { remarks : remarks },
                            dataType: 'json'
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            if(response.success)
                                Swal.fire('Success!', response.message, 'success')
                                    .then(() => window.location.href = "{{ route('stocks.index') }}");
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
            @endcan

            function showDocument() {
                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('stocks.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'number', 'value': number }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'number_list', 'value': numberList }));

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