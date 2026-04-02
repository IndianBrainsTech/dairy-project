@extends('app-layouts.admin-master')

@section('title', $stock ? 'Edit Stock' : 'Create Stock')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        /* Stock Form Field Sizes */
        .field-document-number {
            max-width: 90px;
        }
        .field-qty {
            max-width: 80px;
        }
        .field-unit {
            max-width: 80px;
        }
        .field-batch {
            max-width: 130px;
        }

        /* Mobile-friendly widths */
        @media (max-width: 576px) {
            .field-document-number,
            .field-qty,
            .field-unit,
            .field-batch {
                max-width: 100%;
            }
        }

        /* Make buttons match form-control height */
        .btn-match {
            height: 100%;
        }

        .table-fixed-rows th,
        .table-fixed-rows td {
            height: 32px;
            vertical-align: middle;
            padding: 4px 8px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') {{ $stock ? 'Edit Stock' : 'Create Stock' }} @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Stocks @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-10">
                <div class="card">
                    <div class="card-body">

                        <div class="row px-2 mb-2 flex-wrap align-items-stretch">
                            {{-- Hidden input for stock id (used for edit) --}}
                            @if($stock)
                                <input type="hidden" id="stock-id" value="{{ $stock->id }}">
                            @endif

                            <!-- Document Number -->
                            <div class="col-auto p-1">
                                <input type="text" value="{{ $documentNumber }}" class="form-control field-document-number text-center" readonly>
                            </div>

                            <!-- Item Field (Fluid) -->
                            <div class="col p-1">
                                <div class="input-group w-100 h-100">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-info btn-match" aria-label="Search Item" tabindex="-1">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <input type="text" id="act-item-name" class="form-control" placeholder="Item" tabindex="1">
                                    <input type="hidden" id="hdn-item-id">
                                </div>
                            </div>

                            <!-- Qty -->
                            <div class="col-auto p-1">
                                <input type="text" id="txt-qty" class="form-control field-qty text-center" placeholder="Qty" tabindex="2">
                            </div>

                            <!-- Unit -->
                            <div class="col-auto p-1">
                                <select id="ddl-unit" class="form-control field-unit" tabindex="3">
                                    <option value="">Unit</option>
                                </select>
                            </div>

                            <!-- Batch -->
                            <div class="col-auto p-1">
                                <input type="text" id="txt-batch" class="form-control field-batch" placeholder="Batch Number" tabindex="4">
                            </div>

                            <!-- Save Button -->
                            <div class="col-auto p-1">
                                <button id="btn-save" type="button" class="btn btn-info btn-match" aria-label="Save Item" tabindex="5">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            <!-- Clear Button -->
                            <div class="col-auto p-1">
                                <button id="btn-clear" type="button" class="btn btn-warning btn-match" aria-label="Clear Fields" tabindex="6">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive dash-social mt-3 mb-2">
                            <table id="tbl-stocks" class="table table-bordered table-sm table-fixed-rows">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-center">Item Name</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-left">Unit</th>
                                        <th class="text-center">Batch Number</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($stock && $stock->items->count())
                                        @foreach($stock->items as $index => $item)
                                            <tr data-record-id="{{ $item->id }}" data-item-id="{{ $item->item_id }}" data-unit-id="{{ $item->unit_id }}">
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>{{ $item->item_name }}</td>
                                                <td class="text-right">{{ (float) $item->quantity }}</td>
                                                <td>{{ $item->unit->display_name }}</td>
                                                <td>{{ $item->batch_number }}</td>
                                                <td class="text-center py-0">
                                                    <button type="button" class="td-edit-item btn btn-link p-0 mr-1" title="Edit"><i class="fas fa-edit text-info font-16"></i></button>
                                                    <button type="button" class="td-delete-item btn btn-link p-0 ml-1" title="Delete"><i class="fas fa-trash-alt text-warning font-16"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="empty-row">
                                            <td colspan="6" class="text-center text-muted">No items added yet</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row float-right">
                                    <button type="button" id="btn-reset" class="btn btn-warning mx-2">Clear</button>
                                    <button type="button" id="btn-submit" class="btn btn-primary mx-3" data-toggle="tooltip" data-placement="top" title="{{ $stock ? 'Alt+U' : 'Alt+S' }}">{{ $stock ? 'Update' : 'Submit' }}</button>
                                </div>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
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

            const items = @json($items);
            const units = @json($units);
            const itemUnits = @json($itemUnits);
            const submitShortCut = @json($stock ? 'U' : 'S');

            // Create a map for quick lookup by ID
            const itemsMap = new Map(items.map(item => [item.id, item.name]));

            // Reverse map for quick lookup by Name
            const itemNameMap = new Map(items.map(item => [item.name, item.id]));

            // Create a map for unit (display) name
            const unitsMap = new Map(units.map(unit => [unit.id, unit.display_name]));

            // Create a map for unit hot key
            const hotKeyMap = new Map(units.map(unit => [unit.hot_key.toUpperCase(), unit.id]));

            const showWarning = msg => Swal.fire('Sorry!', msg, 'warning');            

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();
                $('#btn-save').on('click', saveItem);
                $('#btn-clear').on('click', clearFields);
                $('body').on('click', '.td-edit-item', editItem);
                $('body').on('click', '.td-delete-item', deleteItem);
                $('#btn-reset').on('click', resetForm);
                $('#btn-submit').on('click', submitForm);
            }

            // Initialize autocomplete of items
            $("#act-item-name").autocomplete({
                source: autocompleteSource(itemsMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const name = ui.item.value;
                    console.log(`Selected ID: ${id}, Name: ${name}`);
                    $('#hdn-item-id').val(id);
                    loadItemUnits(id);
                    $("#txt-qty").focus();
                }
            });

            // Handle Enter key → move to qty field
            $("#act-item-name").on("keydown", function (e) {
                if (e.key === "Enter") {
                    $("#txt-qty").focus();
                }
            });

            $('#act-item-name').on('blur', function () { 
                let itemName = $('#act-item-name').val().trim();
                if(itemName === "") {
                    // If empty, reset hidden ID
                    $('#hdn-item-id').val(0);
                }
                else if(!itemNameMap.has(itemName)){
                    // Item name not found in map
                    const itemId = parseInt($('#hdn-item-id').val(), 10);

                    if(itemsMap.has(itemId)) {
                        // Restore correct name based on ID
                        itemName = itemsMap.get(itemId);
                        $('#act-item-name').val(itemName);
                    }
                    else {
                        // If ID also invalid, reset fields
                        $(this).val("");
                        $('#hdn-item-id').val(0);
                    }
                }
            });

            $(document).on('keydown', function (e) {
                if (e.altKey && e.key.toUpperCase() === submitShortCut) {
                    e.preventDefault(); // Prevent browser default behavior
                    submitForm();
                }
            });

            $('#txt-qty').on('keydown', function (e) {
                const key = e.key.toUpperCase();

                // 1. Allow control/navigation keys
                const allowedKeys = ['BACKSPACE', 'DELETE', 'ARROWLEFT', 'ARROWRIGHT', 'HOME', 'END', 'TAB'];
                if (allowedKeys.includes(e.key.toUpperCase())) {
                    return; // allow
                }

                // 2. Allow only digits or a single decimal
                if (/^\d$/.test(e.key) || (e.key === '.' && !$(this).val().includes('.'))) {
                    return; // allow
                }

                // 3. If hot_key pressed, update unit selection instantly
                if (hotKeyMap.has(key)) {
                    $('#ddl-unit').val(hotKeyMap.get(key));
                    e.preventDefault(); // block letter in qty field
                    return;
                }

                // 4. If Enter pressed, move to batch field
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#txt-batch').focus();
                    return;
                }

                // 5. Block everything else
                e.preventDefault();
            });

            $("#txt-batch").on("keydown", function (e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    $("#btn-save").focus();
                }
            });

            function loadItemUnits(itemId) {
                const ddlUnit = $('#ddl-unit');
                ddlUnit.empty(); // clear existing options

                // Ensure both sides are the same type
                const id = parseInt(itemId, 10);

                // Filter only the units belonging to this product
                const unitsForItem = itemUnits.filter(unit => unit.product_id === id);

                // Populate dropdown
                unitsForItem.forEach(unit => {
                    ddlUnit.append(
                        $('<option>', {
                            value: unit.unit_id,
                            text: unitsMap.get(unit.unit_id)
                        })
                    );
                });
            }

            function saveItem() {
                const itemId   = $('#hdn-item-id').val();
                const itemName = $('#act-item-name').val().trim();
                const qty      = $('#txt-qty').val();
                const unitId   = $('#ddl-unit').val();
                const unitName = $('#ddl-unit option:selected').text();
                const batch    = $('#txt-batch').val().trim() || null;

                if(!itemName) return showWarning('Please enter item');
                if(!qty) return showWarning('Please enter quantity');
                if(parseFloat(qty) === 0) return showWarning('Quantity should not be zero');
                if(!unitId) return showWarning('Please select unit');

                const $editingRow = $("#tbl-stocks tbody tr.editing");
                const editingItemId = $editingRow.data("item-id");
                let isDuplicate = false;

                // Duplicate check (skip current editing row)
                $("#tbl-stocks tbody tr").each(function () {
                    // Skip rows having the same item-id as the editing row
                    if (editingItemId && $(this).data("item-id") == editingItemId) 
                        return;

                    // Check for duplicate of the new itemId
                    if ($(this).data('item-id') == itemId) {
                        isDuplicate = true;
                        return false; // break
                    }
                });

                if (isDuplicate) {
                    Swal.fire({
                        title: 'Duplicate Item',
                        text: 'This item already exists. Do you want to add it again?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No'
                    })
                    .then((result) => {
                        if (result.value)
                            doSave();
                        else
                            clearFields();
                    });
                } 
                else {
                    doSave();
                }

                // Inner function
                function doSave() {
                    if ($editingRow.length) {
                        // Update existing row
                        $editingRow
                            .data('item-id', itemId)
                            .attr("data-item-id", itemId)
                            .data('unit-id', unitId)
                            .attr("data-unit-id", unitId)
                            .find('td:eq(1)').text(itemName).end()
                            .find('td:eq(2)').text(qty).end()
                            .find('td:eq(3)').text(unitName).end()
                            .find('td:eq(4)').text(batch).end()
                            .removeClass('editing');
                    }
                    else {
                        // Remove placeholder row if exists
                        $("#tbl-stocks tbody .empty-row").remove();

                        // Create new row
                        const $tr = $('<tr>', {
                            'data-record-id': -1,
                            'data-item-id': itemId,
                            'data-unit-id': unitId,
                        });

                        // Add data cells
                        $tr.append(
                            $('<td>', { class: 'text-center' }),
                            $('<td>').text(itemName),
                            $('<td>', { class: 'text-right' }).text(qty),
                            $('<td>').text(unitName),
                            $('<td>').text(batch),
                            $('<td>', { class: 'text-center py-0' }).append(
                                $('<button>', {
                                    type: 'button',
                                    class: 'td-edit-item btn btn-link p-0 mr-1',
                                    title: 'Edit'
                                }).append($('<i>', { class: 'fas fa-edit text-info font-16' })),

                                $('<button>', {
                                    type: 'button',
                                    class: 'td-delete-item btn btn-link p-0 ml-1',
                                    title: 'Delete'
                                }).append($('<i>', { class: 'fas fa-trash-alt text-warning font-16' }))
                            )
                        );

                        // Add row to table and reindex serial number
                        $("#tbl-stocks tbody").append($tr);
                        reindexTable('#tbl-stocks');
                    }

                    // Reset fields and set focus to item
                    clearFields();
                    $('#act-item-name').focus();
                }
            }

            function editItem() {
                const row      = $(this).closest("tr");
                const itemId   = row.data("item-id");
                const unitId   = row.data("unit-id");
                const itemName = row.find("td:eq(1)").text();
                const qty      = row.find("td:eq(2)").text();
                const batch    = row.find("td:eq(4)").text();

                // Set form fields
                loadItemUnits(itemId);
                $("#hdn-item-id").val(itemId);
                $("#act-item-name").val(itemName);
                $("#txt-qty").val(qty);
                $("#txt-batch").val(batch);
                $("#ddl-unit").val(unitId);

                // Mark this row as "editing"
                $("#tbl-stocks tbody tr").removeClass("editing");
                row.addClass("editing");
            }

            function deleteItem() {
                $(this).closest('tr').remove();
                reindexTable('#tbl-stocks');

                // If no rows left, add placeholder row
                const tbody = $("#tbl-stocks tbody");
                if (tbody.find("tr").length === 0) {
                    tbody.append(`
                        <tr class="empty-row">
                            <td colspan="6" class="text-center text-muted">No items added yet</td>
                        </tr>
                    `);
                }
            }

            function clearFields() {
                $('#hdn-item-id').val('');
                $('#act-item-name').val('');
                $('#txt-qty').val('');
                $('#txt-batch').val('');
                $('#ddl-unit').empty();
                $('#ddl-unit').append(new Option('Unit', ''));
                $("#tbl-stocks tbody tr").removeClass("editing");
            }

            function resetForm() {
                clearFields();

                // Clear table body
                const $tbody = $("#tbl-stocks tbody");
                $tbody.empty();

                // Add placeholder empty row
                $tbody.append(
                    $('<tr>', { class: 'empty-row' }).append(
                        $('<td>', { colspan: 6, class: 'text-center text-muted' })
                            .text('No items added')
                    )
                );
            }

            function submitForm() {
                const stockItems = getStockItems();
                if(!stockItems) {
                    showWarning('Please add at least one item before submit.');
                    return;
                }

                const stockId = $('#stock-id').val(); // null if create
                const url = stockId 
                    ? "{{ route('stocks.update', ['stock' => '__ID__']) }}".replace('__ID__', stockId)
                    : "{{ route('stocks.store') }}";

                const method = stockId ? 'PUT' : 'POST';

                $('#btn-submit').prop('disabled', true);
                $.ajax({
                    url: url,
                    type: method,
                    data: { items : stockItems },
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
                    $('#btn-submit').prop('disabled', false);
                    if (!stockId) resetForm(); // only reset after create
                });
            }

            function getStockItems() {
                let stockItems = [];

                // Select all table rows except the placeholder row
                const $rows = $('#tbl-stocks tbody tr').not('.empty-row');

                // If there are no actual rows, return null
                if ($rows.length === 0) {
                    return null;
                }

                $rows.each(function() {
                    stockItems.push({
                        record_id : $(this).data('record-id'),
                        item_id   : $(this).data('item-id'),
                        item_name : $(this).find('td:nth-child(2)').text(),
                        qty       : $(this).find('td:nth-child(3)').text(),
                        unit_id   : $(this).data('unit-id'),
                        batch     : $(this).find('td:nth-child(5)').text()
                    });
                });

                return stockItems;
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop