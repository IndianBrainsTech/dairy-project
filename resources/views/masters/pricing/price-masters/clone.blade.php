@extends('app-layouts.admin-master')

@section('title', 'Clone Price Master')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-styles/pricing.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page Header: Title & Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Clone Price Master @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Deals & Pricing @endslot
                    @slot('item3') Price Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-lg-6">
                                @include('app-partials.deals-pricing-partial', [
                                    'documentNumber' => $document_number,
                                    'documentDate'   => date('Y-m-d'),
                                    'effectDate'     => null,                                    
                                    'narration'      => $master->narration ?? '',
                                ])
                            </div>

                            <div class="col-lg-6">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <h4 class="header-title mt-0">Price List</h4>
                                    </div>
                                    <div class="col-6" style="margin-top:-5px; margin-bottom:10px; text-align:right">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-purple py-1">
                                                <input type="radio" id="rdo-collapse" tabindex="4">Collapse
                                            </label>
                                            <label class="btn btn-outline-purple py-1">
                                                <input type="radio" id="rdo-expand" tabindex="5">Expand
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive table-container" style="max-height:400px">
                                    <table id="tbl-products" class="table table-bordered table-sm">
                                        <thead class="thead-light" style="height:36px">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th>Product</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>
                                                        <input type="text" 
                                                            data-product-id="{{ $product->id }}" 
                                                            class="form-control amount-field" 
                                                            maxlength="8" 
                                                            tabindex="{{ $loop->iteration + 5 }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" 
                                            id="btn-submit" 
                                            class="btn btn-primary float-right mt-4 px-4">
                                                Submit
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div><!--end row-->
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->

    @include('app-partials.modal-customers-selection')
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

            const $dataTable = $('#datatable');
            let table = $dataTable.DataTable({
                dom: 't',
                paging: false,
            });

            const $tblCustomers = $('#tbl-customers');
            const $tblBody      = $('#tbl-customers tbody');
            const $rdoCollapse  = $('#rdo-collapse');
            const $rdoExpand    = $('#rdo-expand');
            const $btnSubmit    = $('#btn-submit');
            const showWarning   = msg => Swal.fire('Sorry!', msg, 'warning');

            doInit();

            function doInit() {
                setMenuItemActive('Masters','ul-deals-pricing','li-price-master');
                restrictToFloatNumbers('.amount-field');

                $rdoCollapse.on('change', function () {
                    if (this.checked) collapseInputList();
                });

                $rdoExpand.on('change', function () {
                    if (this.checked) $('#tbl-products tr').show();
                });

                loadCustomersAndPriceData();
                $rdoCollapse.trigger('click');

                $('#btn-choose-customers').on('click', showCustomerSelectionModal);
                $('#btn-add-customers').on('click', addSelectedCustomers);
                $tblBody.on("click", ".btn-delete-row", deleteRow);
                $btnSubmit.on('click', doSubmit);
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

            function collapseInputList() {
                $(".amount-field").each(function (index, element) {
                    let value = $(this).val();
                    if(!value) {
                        let $row = $(this).closest('tr');
                        $row.hide();
                    }
                });
            }

            // Add a text input to each header cell (from 2nd column onward)
            $dataTable.find('thead tr:first-child th:nth-child(n+2)').each(function (index) {
                const title = $(this).text().trim();
                $(this).html(`<input type="text" class="app-control" placeholder="${title}" data-index="${index}">`);
            });

            // Column-wise filtering
            $(table.table().container()).on('keyup', 'thead tr:first-child input', function () {
                const columnIndex = $(this).data('index'); // Current column index
                table
                    .column(columnIndex + 1) // Target the next column (skip first column)
                    .search(this.value) // Perform the search in the next column
                    .draw();
            } );

            // Prevent sorting when clicking inside filter inputs
            $('#datatable thead tr th input[type="text"]').on('click', function(event) {
                // Stop the event propagation to prevent sorting
                event.stopPropagation();
            });

            function loadCustomersAndPriceData() {
                const customers = @json($master->associated_customers);
                customers.forEach(element => {
                    addTableRow(element.id, element.customer_name);
                });

                const prices = @json($master->associated_products);
                prices.forEach(item => {
                    $(`input[data-product-id="${item.id}"]`).val(item.price);
                });
            }

            function showCustomerSelectionModal() {
                // Clear filter inputs
                $('#datatable thead tr:first-child input').val('');

                // Clear all column searches and redraw
                table.columns().search('').draw();

                // Reset checkboxes and rows
                $('.chk-customer:checked').prop('checked', false);
                $('#datatable tbody tr').show();

                // Hide rows whose IDs are already exists
                let existingIds = getCustomerIds();
                $('#datatable tbody tr').filter(function () {
                    const rowId = parseInt($(this).data('id'), 10);
                    return existingIds.includes(rowId);
                }).hide();
            }

            function addSelectedCustomers() {
                $('.chk-customer:checked').each(function (index, element) {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    addTableRow(id,name);
                });
            }

            // Function to add a row to the table
            function addTableRow(id, name) {
                // Get the current row count
                const rowCount = $tblBody.children('tr').length;

                // Show table, if already hidden
                if(rowCount === 0)
                    $tblCustomers.show();

                // Calculate the new S.No
                const sno = rowCount + 1;
 
                // Create new row
                const $row = $('<tr>', { 'data-id': id });

                // Add cells to the new row
                $('<td>', { 
                    text: sno,
                    class: 'text-center', 
                }).appendTo($row);

                $('<td>', { 
                    text: name 
                }).appendTo($row);

                $('<td>', { 
                    class: 'text-center py-0' 
                }).append(
                    $('<button>', {
                        type: 'button',
                        class: 'btn-delete-row btn btn-link p-0',
                        title: 'Delete Row'
                    }).append($('<i>', { class: 'fas fa-trash-alt text-danger font-16' }))
                ).appendTo($row);
                
                // Append the new row to the table body
                $tblBody.append($row);
            }

            // Event delegation to handle delete button clicks
            function deleteRow() {
                // Remove the row containing the delete button
                $(this).closest('tr').remove();

                const rowCount = $('#tbl-customers tbody tr').length;
                if(rowCount == 0)
                    // Hide table if has no rows
                    $tblCustomers.hide();
                else
                    // Update the serial numbers
                    updateSerialNumbers();
            }

            // Function to update the serial numbers (S.No) in the table
            function updateSerialNumbers() {
                $('#tbl-customers tbody tr').each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            }

            // Function to collect all IDs from 'tbl-customers'
            function getCustomerIds() {
                return $('#tbl-customers tbody tr')
                    .map(function () {
                        const id = $(this).data('id');
                        return parseInt(id, 10);
                    })
                    .get();
            }

            function getPriceList() {
                const priceList = {};

                $('[data-product-id]').each(function () {
                    const productId = parseInt($(this).data('product-id'), 10);
                    const price = parseFloat($(this).val());

                    if (!isNaN(productId) && !isNaN(price) && price >= 0) {
                        priceList[productId] = price;
                    }
                });

                return priceList;
            }

            function doSubmit() {
                const effectDate  = $("#dt-effect").val();
                const narration   = $("#txt-narration").val();
                const customerIds = getCustomerIds();
                const priceList   = getPriceList();

                if(!effectDate)
                    return showWarning('Please Select Effect Date');
                if(!narration)
                    return showWarning('Please Give Narration');
                if(customerIds.length == 0)
                    return showWarning('Please Add Applicable Customers');
                if(Object.keys(priceList).length == 0)  
                    return showWarning('Please Update Price List');

                // Prepare JSON data
                const requestData = {
                    effect_date  : effectDate,
                    narration    : narration,
                    customer_ids : customerIds,
                    price_list   : priceList,
                };

                $btnSubmit.prop('disabled', true);
                $.ajax({
                    url: "{{ route('price-masters.clone.update', $master) }}",
                    type: 'PUT',
                    data: JSON.stringify(requestData),
                    contentType: 'application/json',
                    dataType: 'json',
                    processData: false,
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        Swal.fire('Success!', response.message, 'success')
                            .then(() => window.location.replace("{{ route('price-masters.index') }}"));
                    } else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnSubmit.prop('disabled', false);
                });
            }
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop