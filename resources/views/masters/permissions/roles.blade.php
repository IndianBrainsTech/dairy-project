@extends('app-layouts.admin-master')

@section('title', 'Roles')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Roles @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Permissions @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-pink btn-round font-weight-medium px-3">
                                {{ $roles->count() }} {{ Str::plural('Role', $roles->count()) }}
                            </button>
                            <button type="button" id="btn-add" class="btn btn-gradient-primary px-3" data-toggle="modal" data-target="#modal-role" data-animation="bounce">
                                <i class="mdi mdi-plus-circle-outline mr-2"></i>Add Role
                            </button>
                        </div>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-left pl-2">Role</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        @if($role->name !== \App\Enums\Roles::MASTER_ADMIN)
                                            <tr>
                                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                                <td class="text-left pl-2">{{ $role->display_name }}</td>
                                                <td class="text-center">
                                                    <a href="javascript:void(0)" class="btn-edit mx-2" data-id="{{ $role->id }}" data-toggle="modal" data-target="#modal-role" data-animation="bounce"><i class="fas fa-edit text-info font-16"></i></a>
                                                    <a href="javascript:void(0)" class="btn-delete mx-2" data-id="{{ $role->id }}" data-name="{{ $role->name }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->

    <!-- Start of Role Modal -->
    <div class="modal fade" id="modal-role" tabindex="-1" role="dialog" aria-labelledby="modalRoleLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal-title" class="modal-title mt-0">Add Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm-role" autocomplete="off">
                    <input type="hidden" id="hdn-role-id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="txt-role-name" class="col-sm-4 col-form-label">Role Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" id="txt-role-name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary mx-2 px-3" id="btn-submit">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Role Modal -->
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

            doInit();

            function doInit() {
                $('#datatable').DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',
                });

                $('body')
                    .on('click', '#btn-add', doAdd)
                    .on('click', '.btn-edit', doEdit)
                    .on('click', '.btn-delete', doDelete)
                    .on('click', '#btn-submit', doSubmit);

                $('#modal-role').on('hidden.bs.modal', resetModal);
            }

            function doAdd() {
                $('#modal-title').html("Add Role");
                $('#hdn-role-id').val("");
                $('#txt-role-name').val("");
                $('#btn-submit').text("Add");
                $('#modal-role').modal('show');
            }

            function doEdit() {
                const id = $(this).data('id');                
                $.ajax({
                    url: "{{ route('permissions.roles.edit', ['role' => '__ID__']) }}".replace('__ID__', id),
                    method: "GET",
                    dataType: "json"
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    const data = response.data;
                    $('#modal-title').html("Edit Role");
                    $('#hdn-role-id').val(data.id);
                    $('#txt-role-name').val(data.display_name);
                    $('#btn-submit').text("Update");
                    $('#modal-role').modal('show');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function doDelete() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete the role '${name}'?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('permissions.roles.destroy', ['role' => '__ID__']) }}".replace('__ID__', id),
                            method: 'DELETE',
                            dataType: "json"
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            Swal.fire('Deleted!','Role has been deleted.','success')
                                .then(() => window.location.reload(true));
                        })
                        .fail((xhr, status, error) => {
                            handleAjaxError(xhr, status, error);
                        });                           
                    }
                });
            }

            function doSubmit(event) {
                event.preventDefault();

                const id = $("#hdn-role-id").val();
                const name = $('#txt-role-name').val()?.trim();
                if(!name) {
                    return;
                }

                let url, method;
                if(!id) {
                    url = "{{ route('permissions.roles.store') }}";
                    method = "POST";
                }
                else {
                    url = "{{ route('permissions.roles.update', ['role' => '__ID__']) }}".replace('__ID__', id);
                    method = "PUT";
                }

                $('#btn-submit').prop('disabled', true);

                $.ajax({
                    url: url,
                    method: method,
                    data: { name: name },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    $('#frm-role').trigger("reset");
                    $('#modal-role').modal('hide');
                    Swal.fire('Success!', response.message ,'success')
                        .then(() => window.location.reload(true));
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                    $('#btn-submit').prop('disabled', false);
                });                    
            }

            function resetModal() {
                $('#frm-role').trigger("reset");
                $('#hdn-role-id').val('');
                $('#txt-role-name').val('');
                $('#modal-title').text('Add Role');
                $('#btn-submit').text('Add');
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