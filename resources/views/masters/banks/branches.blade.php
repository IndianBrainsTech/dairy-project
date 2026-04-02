@extends('app-layouts.admin-master')

@section('title', 'Branches')

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
                    @slot('title') Branches @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Banks @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-9">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-pink btn-round font-weight-medium px-3">
                                {{ $branches->count() }} {{ Str::plural('Branch', $branches->count()) }}
                            </button>
                            <button type="button" id="btn-add" class="btn btn-gradient-primary px-3" data-toggle="modal" data-target="#modal-branch" data-animation="bounce">
                                <i class="mdi mdi-plus-circle-outline mr-2"></i>Add Branch
                            </button>
                        </div>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-left pl-2">Bank Name</th>
                                        <th class="text-left pl-2">Branch</th>
                                        <th class="text-center">IFSC</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $branch)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2">{{ $branch->bank->name }}</td>
                                            <td class="text-left pl-2">{{ $branch->name }}</td>
                                            <td class="text-center">{{ $branch->ifsc }}</td>
                                            <td class="text-center">
                                                <a href="javascript:void(0)" class="btn-edit mx-2" data-id="{{ $branch->id }}" data-toggle="modal" data-target="#modal-branch" data-animation="bounce"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="javascript:void(0)" class="btn-delete mx-2" data-id="{{ $branch->id }}" data-name="{{ $branch->name }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of Branch Modal -->
    <div class="modal fade" id="modal-branch" tabindex="-1" role="dialog" aria-labelledby="modalBranchLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 450px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal-title" class="modal-title mt-0">Add Branch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm-branch" autocomplete="off">
                    <input type="hidden" id="hdn-branch-id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label for="ddl-bank" class="col-3 col-form-label">Bank <small class="text-danger font-13">*</small></label>
                                    <div class="col-9">
                                        <select id="ddl-bank" class="form-control" required>
                                            <option value="0">Select Bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="txt-branch-name" class="col-3 col-form-label">Branch <small class="text-danger font-13">*</small></label>
                                    <div class="col-9">
                                        <input type="text" id="txt-branch-name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="txt-ifsc" class="col-3 col-form-label">IFSC <small class="text-danger font-13">*</small></label>
                                    <div class="col-9">
                                        <input type="text" id="txt-ifsc" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
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
    <!-- End of Branch Modal -->
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

                $('#modal-branch').on('hidden.bs.modal', resetModal);
            }

            function doAdd() {
                $('#modal-title').html("Add Branch");
                $('#hdn-branch-id').val("");
                $('#ddl-bank').val(0);
                $('#txt-branch-name').val("");
                $('#txt-ifsc').val("");
                $('#btn-submit').text("Add");
                $('#modal-branch').modal('show');
            }

            function doEdit() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ route('banks.branches.edit', ['branch' => '__ID__']) }}".replace('__ID__', id),
                    method: "GET",
                    dataType: "json"
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    const data = response.data;
                    $('#modal-title').html("Edit Branch");
                    $('#hdn-branch-id').val(data.id);
                    $('#ddl-bank').val(data.bank_id);
                    $('#txt-branch-name').val(data.name);
                    $('#txt-ifsc').val(data.ifsc);
                    $('#btn-submit').text("Update");
                    $('#modal-branch').modal('show');
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
                    text: `Do you want to delete the branch '${name}'?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('banks.branches.destroy', ['branch' => '__ID__']) }}".replace('__ID__', id),
                            method: 'DELETE',
                            dataType: "json"
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            Swal.fire('Deleted!','Branch has been deleted.','success')
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

                const id     = $("#hdn-branch-id").val();
                const bankId = $("#ddl-bank").val();
                const name   = $('#txt-branch-name').val()?.trim();
                const ifsc   = $('#txt-ifsc').val()?.trim();

                if(bankId == 0) {
                    Swal.fire('Attention!', 'Please Select Bank' ,'warning')
                    return;
                }
                if(!name) {
                    Swal.fire('Attention!', 'Please Enter Branch' ,'warning')
                    return;
                }
                if(!ifsc) {
                    Swal.fire('Attention!', 'Please Enter IFSC' ,'warning')
                    return;
                }

                let url, method;
                if(!id) {
                    url = "{{ route('banks.branches.store') }}";
                    method = "POST";
                }
                else {
                    url = "{{ route('banks.branches.update', ['branch' => '__ID__']) }}".replace('__ID__', id);
                    method = "PUT";
                }

                $('#btn-submit').prop('disabled', true);

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        bank_id : bankId,
                        name    : name,
                        ifsc    : ifsc,
                    },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    $('#frm-branch').trigger("reset");
                    $('#modal-branch').modal('hide');
                    Swal.fire('Success!', response.message ,'success')
                        .then(() => window.location.reload(true));
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                    $('#btn-submit').prop('disabled', false);
                });
            }

            function resetModal() {
                $('#frm-branch').trigger("reset");
                $('#hdn-branch-id').val('');
                $('#ddl-bank').val(0);
                $('#txt-branch-name').val('');
                $('#txt-ifsc').val('');
                $('#modal-title').text('Add Branch');
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