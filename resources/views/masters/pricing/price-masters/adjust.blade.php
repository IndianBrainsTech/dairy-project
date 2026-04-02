@extends('app-layouts.admin-master')

@section('title', 'Price Adjustment')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page Header: Title & Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Price Adjustment @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Deals & Pricing @endslot
                    @slot('item3') Price Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <div class="row px-2 mb-2 flex-nowrap align-items-stretch">
                            <!-- Item Field (Fluid) -->
                            <div class="col p-1 flex-fill">
                                <div class="input-group w-100 h-100">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-info btn-match" aria-label="Search Item" tabindex="-1">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <input type="text" id="act-item-name" class="form-control" placeholder="Item" tabindex="1" style="min-width:300px">
                                    <input type="hidden" id="hdn-item-id">
                                </div>
                            </div>

                            <!-- Value -->
                            <div class="col-auto p-1 mx-2">
                                <div class="input-group w-100 h-100">
                                    <div class="input-group-prepend">
                                        <button type="button" id="btn-sign" class="btn btn-secondary" data-mode="plus" aria-pressed="false" tabindex="2">+</button>
                                    </div>
                                    <input type="text" id="txt-amount" class="form-control field-amount text-center" placeholder="Value" tabindex="3" style="width:80px">
                                </div>
                            </div>

                            <!-- Add Button -->
                            <div class="col-auto p-1">
                                <button id="btn-add" type="button" class="btn btn-info px-3" aria-label="Add Item" tabindex="4">
                                    Add
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive dash-social mt-3 mb-1">
                            <table id="tbl-prices" class="table table-bordered table-sm table-fixed-rows">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-left pl-2">Item</th>
                                        <th class="text-center">Value</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="empty-row">
                                        <td colspan="4" class="text-center text-muted">No items added yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-1">
                                    <button type="button" id="btn-reset" class="btn btn-warning btn-sm mr-2">Clear</button>
                                    <button type="button" id="btn-load" class="btn btn-pink btn-sm ml-2" style="display:none">Load Masters</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive dash-social mt-3 mb-1">
                            <table id="tbl-masters" class="table table-bordered table-sm table-fixed-rows" style="display:none">
                                <thead class="thead-light">
                                    <tr>
                                        <th>
                                            <div class="checkbox checkbox-primary checkbox-single d-flex justify-content-center align-items-center">
                                                <input type="checkbox" id="chk-all" value="All" aria-label="Select All">
                                                <label class="mb-0 pl-0"></label>
                                            </div>
                                        </th>
                                        <th class="text-center">Document</th>
                                        <th class="text-center">Effect Date</th>
                                        <th class="text-left pl-2">Narration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        {{-- <div class="row">
                            <div class="col-12">
                                <div class="form-group float-right mb-1">
                                    <button type="button" id="btn-submit" class="btn btn-primary px-3" style="display:none">Submit</button>
                                </div>
                            </div>
                        </div> --}}

                        <div id="div-submit" class="form-group row align-items-end" style="display:none">
                            <div class="col-md-6">
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label text-left">
                                        Effect Date <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="col-sm-6">
                                        <input type="date" class="form-control" id="dt-effect" min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 text-right">
                                <button type="button" id="btn-submit" class="btn btn-primary">Submit</button>
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

            // Create a map for quick lookup by ID
            const itemsMap = new Map(items.map(item => [item.id, item.name]));

            // Reverse map for quick lookup by Name
            const itemNameMap = new Map(items.map(item => [item.name, item.id]));
            
            const showWarning = msg => Swal.fire('Sorry!', msg, 'warning');

            const $hdnItemId   = $('#hdn-item-id');
            const $actItemName = $('#act-item-name');
            const $txtAmount   = $('#txt-amount');
            const $chkAll      = $('#chk-all');
            const $dtEffect    = $('#dt-effect');
            const $btnSign     = $('#btn-sign');
            const $btnAdd      = $('#btn-add');
            const $btnLoad     = $('#btn-load');
            const $btnReset    = $('#btn-reset');
            const $btnSubmit   = $('#btn-submit');
            const $divSubmit   = $('#div-submit');
            const $tblPrices   = $('#tbl-prices');
            const $tblMasters  = $('#tbl-masters');

            doInit();

            function doInit() {
                setMenuItemActive('Masters','ul-deals-pricing','li-price-master');

                $btnAdd.on('click', addItem);
                $btnSign.on('click', toggleSign);
                $txtAmount.on('keydown', validateAmount);
                $btnLoad.on('click', fetchMasters);
                $btnReset.on('click', resetForm);
                $btnSubmit.on('click', submitForm);
                $('body').on('click', '.td-delete-item', deleteItem);

                $chkAll.on('change', function () { 
                    let isChecked = $(this).is(':checked');
                    $('.chk-box').prop('checked', isChecked).trigger('change');
                });
            }

            function setMenuItemActive(menuId, submenuId, menuItemId) {
                // Open main menu (simulate click)
                $('a[href="#Menu' + menuId + '"]').trigger('click');

                // Open submenu
                $('#' + submenuId)
                    .addClass('show')
                    .attr('aria-expanded', 'true')
                    .slideDown(0);

                // Set active class on menu item <li> and <a>
                $('#' + menuItemId).addClass('active');
                $('#' + menuItemId + ' > a').addClass('active');
            }

            // Initialize autocomplete of items
            $actItemName.autocomplete({
                source: autocompleteSource(itemsMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const name = ui.item.value;
                    console.log(`Selected ID: ${id}, Name: ${name}`);
                    $hdnItemId.val(id);
                    $txtAmount.focus();
                }
            });

            $actItemName.on('blur', function () { 
                let itemName = $actItemName.val().trim();
                if(itemName === "") {
                    // If empty, reset hidden ID
                    $hdnItemId.val(0);
                }
                else if(!itemNameMap.has(itemName)){
                    // Item name not found in map
                    const itemId = parseInt($hdnItemId.val(), 10);

                    if(itemsMap.has(itemId)) {
                        // Restore correct name based on ID
                        itemName = itemsMap.get(itemId);
                        $actItemName.val(itemName);
                    }
                    else {
                        // If ID also invalid, reset fields
                        $(this).val('');
                        $hdnItemId.val(0);
                    }
                }
            });

            function toggleSign() {
                const $btn = $(this);
                const mode = $btn.data('mode');

                if (mode === 'plus') {
                    $btn.text('−')
                        .data('mode', 'minus')
                        .removeClass('btn-secondary')
                        .addClass('btn-warning')
                        .attr('aria-pressed', 'true');
                } else {
                    $btn.text('+')
                        .data('mode', 'plus')
                        .removeClass('btn-warning')
                        .addClass('btn-secondary')
                        .attr('aria-pressed', 'false');
                }
            }

            function validateAmount(e) {
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

                // 3. If Enter pressed, move to batch field
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $btnAdd.focus();
                    return;
                }

                // 4. Block everything else
                e.preventDefault();
            }

            function addItem() {
                const itemId   = $hdnItemId.val();
                const itemName = $actItemName.val().trim();
                const amount   = $txtAmount.val();

                if(!itemName) return showWarning('Please enter item');
                if(!amount) return showWarning('Please enter value');
                if(parseFloat(amount) === 0) return showWarning('Value should not be zero');

                let isDuplicate = false;

                // Duplicate check
                $("#tbl-prices tbody tr").each(function () {
                    // Check for duplicate of the new itemId
                    if ($(this).data('item-id') == itemId) {
                        isDuplicate = true;
                        return false; // break
                    }
                });

                if (isDuplicate) {
                    return showWarning('This item already exists');
                }

                // Remove placeholder row if exists
                $("#tbl-prices tbody .empty-row").remove();

                let amt = $txtAmount.val().trim();
                if (amt.startsWith('.'))
                    amt = '0' + amt;
                const value = $btnSign.text() + amt;

                // Create new row
                const $tr = $('<tr>', {
                    'data-item-id': itemId,
                });

                // Add data cells
                $tr.append(
                    $('<td>', { class: 'text-center' }),
                    $('<td>').text(itemName),
                    $('<td>', { class: 'text-center' }).text(value),
                    $('<td>', { class: 'text-center py-0' }).append(
                        $('<button>', {
                            type: 'button',
                            class: 'td-delete-item btn btn-link p-0 ml-1',
                            title: 'Delete'
                        }).append($('<i>', { class: 'fas fa-trash-alt text-warning font-16' }))
                    )
                );

                // Add row to table and reindex serial number
                $("#tbl-prices tbody").append($tr);
                reindexTable('#tbl-prices');

                // Reset fields and set focus to item
                $hdnItemId.val('');
                $actItemName.val('');
                $txtAmount.val('');
                clearMasters();
                $btnLoad.show();
                $actItemName.focus();
            }

            function deleteItem() {
                $(this).closest('tr').remove();
                reindexTable('#tbl-prices');

                // If no rows left, add placeholder row
                const tbody = $("#tbl-prices tbody");
                if (tbody.find("tr").length === 0) {
                    tbody.append(`
                        <tr class="empty-row">
                            <td colspan="6" class="text-center text-muted">No items added yet</td>
                        </tr>
                    `);

                    $btnLoad.hide();
                }

                clearMasters();
            }

            function fetchMasters() {
                const itemIds = getItemIds();
                if(!itemIds) {
                    showWarning('Please add at least one item.');
                    return;
                }

                $btnLoad.prop('disabled', true);
                $.ajax({
                    url: "{{ route('price-masters.adjust.fetch') }}",
                    type: 'GET',
                    data: { item_ids : itemIds },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        if(response.masters.length > 0)
                            loadMasters(response.masters);
                        else
                            showWarning('No price master associates with the item(s).');
                    }
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnLoad.prop('disabled', false);
                });
            }

            function loadMasters(masters)
            {
                let $tblBody = $("#tbl-masters tbody");
                $tblBody.empty();

                masters.forEach(master => {
                    let row = `
                        <tr>
                            <td>
                                <div class="checkbox checkbox-primary checkbox-single d-flex justify-content-center align-items-center">
                                    <input type="checkbox" class="chk-box" value="${master.id}" aria-label="chk-${master.id}">
                                    <label class="mb-0 pl-0"></label>
                                </div>
                            </td>
                            <td class="text-center">${master.document_number}</td>
                            <td class="text-center">${master.effect_date_for_display}</td>
                            <td class="text-left pl-2">${master.narration}</td>
                        </tr>`;
                    $tblBody.append(row);
                });

                $tblMasters.show();
                $divSubmit.show();
            }

            function resetForm() {
                $hdnItemId.val('');
                $actItemName.val('');
                $txtAmount.val('');

                // Clear table body
                const $tbody = $("#tbl-prices tbody");
                $tbody.empty();

                // Add placeholder empty row
                $tbody.append(
                    $('<tr>', { class: 'empty-row' }).append(
                        $('<td>', { colspan: 6, class: 'text-center text-muted' })
                            .text('No items added yet')
                    )
                );

                $btnSign
                    .text('+')
                    .data('mode', 'plus')
                    .removeClass('btn-warning')
                    .addClass('btn-secondary')
                    .attr('aria-pressed', 'false');

                $btnLoad.hide();
                clearMasters();
            }

            function clearMasters()
            {
                $tblMasters.find('tbody').empty();
                $chkAll.prop('checked', false);
                $tblMasters.hide();
                $divSubmit.hide();
            }

            function submitForm() {
                const priceItems = getPriceItems();
                if(!priceItems) {
                    showWarning('Please add at least one item before submit.');
                    return;
                }

                const selectedIds = $('.chk-box').filter(':checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    showWarning('Please select masters to apply');
                    return;
                }

                const effectDate = $dtEffect.val();
                if(!effectDate) {
                    showWarning('Please give effect date');
                    return;
                }

                // Regex check (YYYY-MM-DD)
                let dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                if (!dateRegex.test(effectDate)) {
                    showWarning('Effect date seems invalid');
                    return false;
                }

                $btnSubmit.prop('disabled', true);
                $.ajax({
                    url: "{{ route('price-masters.adjust.store') }}",
                    type: 'POST',
                    data: { 
                        items : priceItems,
                        ids   : selectedIds,
                        effect_date : effectDate,
                    },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success)
                        Swal.fire('Success!', response.message, 'success')
                            .then(() => window.location.href = "{{ route('price-masters.index') }}");
                    else
                        Swal.fire('Sorry!', response.message, 'error');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnSubmit.prop('disabled', false);
                });
            }

            function getItemIds() {
                const rows = $('#tbl-prices tbody tr').not('.empty-row');
                if (!rows.length) return null;

                return rows.map(function () {
                    return $(this).data('item-id');
                }).get();
            }

            function getPriceItems() {
                let priceItems = [];

                // Select all table rows except the placeholder row
                const $rows = $('#tbl-prices tbody tr').not('.empty-row');

                // If there are no actual rows, return null
                if ($rows.length === 0) {
                    return null;
                }

                $rows.each(function() {
                    priceItems.push({
                        item_id      : $(this).data('item-id'),
                        item_name    : $(this).find('td:nth-child(2)').text(),
                        adjust_value : $(this).find('td:nth-child(3)').text(),
                    });
                });

                return priceItems;
            }
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop