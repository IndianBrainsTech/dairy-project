@extends('app-layouts.admin-master')

@section('title', 'Locations')

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
                @component('app-components.breadcrumb-3')
                    @slot('title') Locations @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Purchase @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        @include('app-partials.master-header', [
                            'countLabel' => 'Location',
                            'count'      => $masters->count(),
                        ])

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="max-width:40px">S.No</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-left pl-2">Location</th>
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
                                            <td class="text-center">{{ $master->code }}</td>
                                            <td class="text-left pl-2">{{ $master->name }}</td>
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

    <!-- Start of Group Modal -->
    <div class="modal fade" id="mdl-form" tabindex="-1" role="dialog" aria-labelledby="mdl-title" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 450px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="mdl-title" class="modal-title mt-0">Location</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm-master" autocomplete="off">
                    <input type="hidden" id="hdn-id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row" id="div-code">
                                    <label for="txt-code" class="col-sm-4 col-form-label">
                                        Code <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" id="txt-code" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="txt-name" class="col-sm-4 col-form-label">
                                        Name <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" id="txt-name" class="form-control" tabindex="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal" tabindex="3">Close</button>
                        <button type="submit" class="btn btn-primary mx-2 px-3" id="btn-submit" tabindex="2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Group Modal -->
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
            const $divCode   = $('#div-code');
            const $btnCreate = $('#btn-create');
            const $btnSubmit = $('#btn-submit');
            const $hdnId     = $('#hdn-id');
            const $txtCode   = $('#txt-code');
            const $txtName   = $('#txt-name');
            const $dataTable = $('#datatable');
            doInit();

            function doInit() {
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
                        window.location.href = "{{ route('locations.index') }}" + "?status=" + encodeURIComponent(newStatus);
                    }
                });

                $btnCreate.on('click', createMaster);
                $btnSubmit.on('click', submitMaster);
                $dataTable.on('click', '.btn-edit', editMaster);
                $dataTable.on('click', '.btn-delete', deleteMaster);
                $mdlForm.on('hidden.bs.modal', resetModal);
            }

            function createMaster() {
                $mdlTitle.html("Create Location");
                $hdnId.val("");
                $txtCode.val("");
                $txtName.val("");
                $btnSubmit.text("Create");
                $divCode.hide();
                $mdlForm.modal('show');
            }

            function editMaster() {
                const row = $(this).closest("tr");
                const id = row.data("id");
                const code = row.find("td:eq(1)").text();
                const name = row.find("td:eq(2)").text();

                $mdlTitle.html("Edit Location");
                $hdnId.val(id);
                $txtCode.val(code);
                $txtName.val(name);
                $btnSubmit.text("Update");
                $divCode.show();
                $mdlForm.modal('show');
            }

            function deleteMaster() {
                const row = $(this).closest("tr");
                const id = row.data("id");
                const name = row.find("td:eq(2)").text();
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete the location '${name}'?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('locations.destroy', ['location' => '__ID__']) }}".replace('__ID__', id),
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

                if(!name) {
                    Swal.fire('Attention!', 'Please Enter Name' ,'warning')
                    return;
                }

                let url, method;
                if(!id) {
                    url = "{{ route('locations.store') }}";
                    method = "POST";
                }
                else {
                    url = "{{ route('locations.update', ['location' => '__ID__']) }}".replace('__ID__', id);
                    method = "PUT";
                }

                $btnSubmit.prop('disabled', true);

                $.ajax({
                    url: url,
                    method: method,
                    data: { name : name },
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

            function resetModal() {
                $frmMaster.trigger("reset");
                $hdnId.val('');
                $txtCode.val('');
                $txtName.val('');
                $mdlTitle.text('Location');
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