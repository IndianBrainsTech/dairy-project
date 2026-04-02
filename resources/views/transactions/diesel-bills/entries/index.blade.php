@extends('app-layouts.admin-master')

@section('title', 'View Diesel Bills')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">    
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Diesel Bills @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Entry @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <form method="get" action="{{ route('diesel-bills.entries.index') }}" aria-label="Diesel bill filter">
                            <div class="row">
                                <input type="date" name="from_date" id="dt-from" value="{{ request('from_date', $dates['from']) }}" class="app-control mx-2">
                                <input type="date" name="to_date" id="dt-to" value="{{ request('to_date', $dates['to']) }}" class="app-control mr-3">
                                <div class="input-group" style="width:350px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name="bunk_name" id="act-bunk-name" class="app-control" placeholder="Petrol Bunk" style="width:300px">
                                    <input type="hidden" name="bunk_id" id="hdn-bunk-id">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-primary btn-sm px-3" aria-label="Submit" title="Submit">
                                <span class="bg-soft-pink rounded mx-2 p-2">Fuel : <b>{{ $totals->fuel }}</b></span>
                                <span class="bg-soft-pink rounded mx-2 p-2">Amount : <b>{{ $totals->amount }}</b></span>
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th class="d-none">ID</th>
                                        <th class="text-center">S.No</th>
                                        <th class="text-center">Document</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-left pl-2">Petrol Bunk</th>
                                        <th class="text-left pl-2">Vehicle Number</th>
                                        <th class="text-right pr-2">Fuel</th>
                                        <th class="text-right pr-2">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bills as $bill)
                                        <tr>
                                            <td class="d-none">{{ $bill->id }}</td>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $bill->document_number }}</td>
                                            <td class="text-center">{{ $bill->document_date }}</td>
                                            <td class="text-left pl-2">{{ $bill->bunk_name }}</td>
                                            <td class="text-left pl-2">{{ $bill->vehicle_number }}</td>
                                            <td class="text-right pr-2">{{ $bill->fuel }}</td>
                                            <td class="text-right pr-2">{{ $bill->amount }}</td>
                                            <td class="text-center">{!! getStatusWithBadge($bill->status->label()) !!}</td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-link btn-icon btn-view"
                                                        data-id="{{ $bill->id }}"
                                                        aria-label="View diesel bill {{ $bill->id }}">
                                                    <i class="dripicons-preview text-primary font-20"></i>
                                                </button>
                                            </td>
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
    <script src="{{ asset('assets/js/helper-v1.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const bunk          = @json($bunk);
            const bunks         = @json($bunks);
            const bunkMap       = new Map(bunks.map(bunk => [bunk.id, bunk.name]));
            const bunkNameMap   = new Map(bunks.map(bunk => [bunk.name, bunk.id]));

            const $actBunkName  = $('#act-bunk-name');
            const $hdnBunkId    = $('#hdn-bunk-id');

            const table = $('#datatable').DataTable({
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                language: {
                    emptyTable: "No diesel bill found for the date range."
                }
            });

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();

                $('#dt-from').on('change', function () {
                    $('#dt-to').attr('min', this.value);
                }).trigger('change');

                $('#datatable').on('click', '.btn-view', viewRecord);

                if (bunk && bunk.id) $hdnBunkId.val(bunk.id);
                if (bunk && bunk.name) $actBunkName.val(bunk.name);
            }

            $actBunkName.autocomplete({
                source: autocompleteSource(bunkMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const name = ui.item.value;
                    console.log(`Selected Bunk => ID: ${id}, Name: ${name}`);
                    $hdnBunkId.val(id);
                }
            });

            $actBunkName.on('change', function () {
                let name = $(this).val().trim();
                if(name == "") {
                    $hdnBunkId.val('');
                }
                else if(!bunkNameMap.has(name)){
                    const id = $hdnBunkId.val();
                    name = bunkMap.get(parseInt(id));
                    $actBunkName.val(name);
                }
            });

            function viewRecord() {
                const id = $(this).data('id');
                const idList = table.column(0,{search:'applied'}).data().toArray();

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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop