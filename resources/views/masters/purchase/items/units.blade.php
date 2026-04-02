@extends('app-layouts.admin-master')

@section('title', 'Purchase Item Units')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page Header: Title & Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Purchase Item Units @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Purchase @endslot
                    @slot('item3') Items @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        @include('app-partials.master-header', [
                            'countLabel' => 'Unit',
                            'count'      => $masters->count(),
                        ])

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="max-width:40px">S.No</th>
                                        <th class="text-left pl-2">Unit</th>
                                        <th class="text-left pl-2" style="max-width:80px">Abbreviation</th>
                                        <th class="text-center">Hot Key</th>
                                        @if($status != "Active")
                                            <th class="text-center">Status</th>
                                        @endif
                                        <th class="text-center" style="max-width:60px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($masters as $master)
                                        <tr data-id="{{ $master->id }}">
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2">{{ $master->name }}</td>
                                            <td class="text-left pl-2">{{ $master->abbreviation }}</td>
                                            <td class="text-center">{{ $master->hot_key }}</td>
                                            @if($status != "Active")
                                                <th class="text-center">{!! getStatusWithBadge($master->status->label()) !!}</th>
                                            @endif
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link btn-icon btn-edit p-0 mx-1" title="Edit">
                                                    <i class="fas fa-edit text-info font-16"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-icon btn-delete p-0 mx-1" title="Delete">
                                                    <i class="fas fa-trash-alt text-danger font-16"></i>
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

    <!-- Start of Unit Modal -->
    <div class="modal fade" id="mdl-form" tabindex="-1" role="dialog" aria-labelledby="mdl-title" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="mdl-title" class="modal-title mt-0">Item Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm-master" autocomplete="off">
                    <input type="hidden" id="hdn-id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label for="txt-name" class="col-sm-4 col-form-label">
                                        Unit Name <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" id="txt-name" class="form-control" tabindex="1" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="txt-abbr" class="col-sm-4 col-form-label">
                                        Abbreviation <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" id="txt-abbr" class="form-control" tabindex="2" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="sel-hot-key" class="col-sm-4 col-form-label">
                                        Hot Key <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="col-sm-8">
                                        <select id="sel-hot-key" class="form-control" tabindex="3" required>
                                            <option value=""></option>
                                            @foreach($availableKeys as $key)
                                                <option value="{{ $key }}">{{ $key }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal" tabindex="5">Close</button>
                        <button type="submit" class="btn btn-primary mx-2 px-3" id="btn-submit" tabindex="4">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Unit Modal -->
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

            const $mdlForm   = $('#mdl-form');
            const $mdlTitle  = $('#mdl-title');
            const $frmMaster = $('#frm-master');
            const $btnCreate = $('#btn-create');
            const $btnSubmit = $('#btn-submit');
            const $hdnId     = $('#hdn-id');
            const $txtName   = $('#txt-name');
            const $txtAbbr   = $('#txt-abbr');
            const $selHotKey = $('#sel-hot-key');
            const $dataTable = $('#datatable');
            doInit();

            function doInit() {
                setMenuItemActive('Masters','ul-purchase-items','li-purchase-item-units');

                $dataTable.DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',
                });

                $('.dropdown-menu .dropdown-item').on('click', function (e) {
                    let newStatus = $(this).text();
                    let oldStatus = $('#btn-status').text().trim();
                    if(newStatus != oldStatus) {
                        window.location.href = "{{ route('purchase.items.units.list') }}" + "?status=" + encodeURIComponent(newStatus);
                    }
                });

                $btnCreate.on('click', createMaster);
                $btnSubmit.on('click', submitMaster);
                $dataTable.on('click', '.btn-edit', editMaster);
                $dataTable.on('click', '.btn-delete', deleteMaster);
                $mdlForm.on('hidden.bs.modal', resetModal);
            }

            function createMaster() {
                $mdlTitle.html("Create Item Unit");
                $hdnId.val("");
                $txtName.val("");
                $txtAbbr.val("");
                $selHotKey.val("");
                $btnSubmit.text("Create");
                $mdlForm.modal('show');
            }

            function editMaster() {
                const row = $(this).closest("tr");
                const id = row.data("id");
                const name = row.find("td:eq(1)").text();
                const abbr = row.find("td:eq(2)").text();
                const hotKey = row.find("td:eq(3)").text();

                updateAvailableHotKeys(hotKey);

                $mdlTitle.html("Edit Item Unit");
                $hdnId.val(id);
                $txtName.val(name);
                $txtAbbr.val(abbr);
                $selHotKey.val(hotKey);
                $btnSubmit.text("Update");
                $mdlForm.modal('show');
            }

            function deleteMaster() {
                const row = $(this).closest("tr");
                const id = row.data("id");
                const name = row.find("td:eq(1)").text();
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete the unit '${name}'?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('purchase.items.units.destroy', ['master' => '__ID__']) }}".replace('__ID__', id),
                            method: 'DELETE',
                            dataType: "json"
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            Swal.fire('Deleted!', response.message, 'success')
                                .then(() => window.location.reload(true));
                        })
                        .fail((xhr, status, error) => {
                            handleAjaxError(xhr, status, error);
                        });
                    }
                });
            }

            function submitMaster(event) {
                event.preventDefault();

                const id = $hdnId.val();
                const name = $txtName.val()?.trim();
                const abbr = $txtAbbr.val()?.trim();
                const hotKey = $selHotKey.val();

                if(!name) {
                    Swal.fire('Attention!', 'Please Enter Unit Name' ,'warning')
                    return;
                }
                if(!abbr) {
                    Swal.fire('Attention!', 'Please Enter Abbreviation' ,'warning')
                    return;
                }
                if(!hotKey) {
                    Swal.fire('Attention!', 'Please Select Hot Key' ,'warning')
                    return;
                }

                let url, method;
                if(!id) {
                    url = "{{ route('purchase.items.units.store') }}";
                    method = "POST";
                }
                else {
                    url = "{{ route('purchase.items.units.update', ['master' => '__ID__']) }}".replace('__ID__', id);
                    method = "PUT";
                }

                $btnSubmit.prop('disabled', true);

                $.ajax({
                    url: url,
                    method: method,
                    data: { 
                        name : name,
                        abbr : abbr,
                        hot_key : hotKey,
                    },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    $frmMaster.trigger("reset");
                    $mdlForm.modal('hide');
                    Swal.fire('Success!', response.message ,'success')
                        .then(() => window.location.reload(true));
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                    $btnSubmit.prop('disabled', false);
                });
            }

            function updateAvailableHotKeys(hotKey) {
                // Remove previously injected edit option (if any)
                $selHotKey.find('option[data-edit="true"]').remove();

                // If option already not present → prepend
                if ($selHotKey.find(`option[value="${hotKey}"]`).length === 0) {
                    $selHotKey.prepend(`<option value="${hotKey}" data-edit="true">${hotKey}</option>`);
                }

                // Select the value
                $selHotKey.val(hotKey).trigger('change');
            }

            function resetModal() {
                $frmMaster.trigger("reset");
                $hdnId.val('');
                $txtName.val('');
                $txtAbbr.val('');
                $selHotKey.val('');
                $mdlTitle.text('Item Unit');
                $btnSubmit.text('Submit');
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop