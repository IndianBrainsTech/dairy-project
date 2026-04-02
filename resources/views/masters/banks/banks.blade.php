@extends('app-layouts.admin-master')

@section('title', 'Banks')

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
                    @slot('title') Banks @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Banks @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-pink btn-round font-weight-medium px-3">
                                {{ $banks->count() }} {{ Str::plural('Bank', $banks->count()) }}
                            </button>
                            <button type="button" id="btn-add" class="btn btn-gradient-primary px-3" data-toggle="modal" data-target="#modal-bank" data-animation="bounce">
                                <i class="mdi mdi-plus-circle-outline mr-2"></i>Add Bank
                            </button>
                        </div>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-left pl-2">Bank Name</th>
                                        <th class="text-center">Short Name</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($banks as $bank)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2">{{ $bank->name }}</td>
                                            <td class="text-center">{{ $bank->short_name }}</td>
                                            <td class="text-center">
                                                <a href="javascript:void(0)" class="btn-edit mx-2" data-id="{{ $bank->id }}" data-toggle="modal" data-target="#modal-bank" data-animation="bounce"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="javascript:void(0)" class="btn-delete mx-2" data-id="{{ $bank->id }}" data-name="{{ $bank->name }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of Bank Modal -->
    <div class="modal fade" id="modal-bank" tabindex="-1" role="dialog" aria-labelledby="modalBankLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal-title" class="modal-title mt-0">Add Bank</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm-bank" autocomplete="off">
                    <input type="hidden" id="hdn-bank-id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label for="txt-bank-name" class="col-4 col-form-label">Bank Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-8">
                                        <input type="text" id="txt-bank-name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="txt-short-name" class="col-4 col-form-label">Short Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-8">
                                        <input type="text" id="txt-short-name" class="form-control" required>
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
    <!-- End of Bank Modal -->
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

                $('#modal-bank').on('hidden.bs.modal', resetModal);
            }

            function doAdd() {
                $('#modal-title').html("Add Bank");
                $('#hdn-bank-id').val("");
                $('#txt-bank-name').val("");
                $('#txt-short-name').val("");
                $('#btn-submit').text("Add");
                $('#modal-bank').modal('show');
            }

            function doEdit() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ route('banks.edit', ['bank' => '__ID__']) }}".replace('__ID__', id),
                    method: "GET",
                    dataType: "json"
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    const data = response.data;
                    $('#modal-title').html("Edit Bank");
                    $('#hdn-bank-id').val(data.id);
                    $('#txt-bank-name').val(data.name);
                    $('#txt-short-name').val(data.short_name);
                    $('#btn-submit').text("Update");
                    $('#modal-bank').modal('show');
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
                    text: `Do you want to delete the bank '${name}'?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('banks.destroy', ['bank' => '__ID__']) }}".replace('__ID__', id),
                            method: 'DELETE',
                            dataType: "json"
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            Swal.fire('Deleted!','Bank has been deleted.','success')
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

                const id = $("#hdn-bank-id").val();
                const name = $('#txt-bank-name').val()?.trim();
                const shortName = $('#txt-short-name').val()?.trim();
                if(!name) {
                    Swal.fire('Attention!', 'Please Enter Bank Name' ,'warning')
                    return;
                }
                if(!shortName) {
                    Swal.fire('Attention!', 'Please Enter Short Name' ,'warning')
                    return;
                }

                let url, method;
                if(!id) {
                    url = "{{ route('banks.store') }}";
                    method = "POST";
                }
                else {
                    url = "{{ route('banks.update', ['bank' => '__ID__']) }}".replace('__ID__', id);
                    method = "PUT";
                }

                $('#btn-submit').prop('disabled', true);

                $.ajax({
                    url: url,
                    method: method,
                    data: { 
                        name: name,
                        short_name: shortName,
                    },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    $('#frm-bank').trigger("reset");
                    $('#modal-bank').modal('hide');
                    Swal.fire('Success!', response.message ,'success')
                        .then(() => window.location.reload(true));
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                    $('#btn-submit').prop('disabled', false);
                });
            }

            function resetModal() {
                $('#frm-bank').trigger("reset");
                $('#hdn-bank-id').val('');
                $('#txt-bank-name').val('');
                $('#txt-short-name').val('');
                $('#modal-title').text('Add Bank');
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