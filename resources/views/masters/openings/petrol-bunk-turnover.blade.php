@extends('app-layouts.admin-master')

@section('title', 'Petrol Bunk Turnover')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/input-style.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Petrol Bunk Turnover @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Openings @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th class="text-left pl-2">Petrol Bunk</th>
                                    <th class="text-left pl-2">TDS Status</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">As per Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($bunks as $bunk)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left pl-2" id="name{{$bunk->id}}">{{ $bunk->name }}</td>
                                            <td class="text-left pl-2">{{ $bunk->tds_status->label() }}</td>
                                            <td style="width:120px"><input type="text" id="amount{{$bunk->id}}" value="{{$bunk->amount}}" data-value="{{$bunk->amount}}" class="form-control amount-cell" maxlength="12" disabled></td>
                                            <td style="width:145px"><input type="date" id="date{{$bunk->id}}" value="{{$bunk->date}}" data-value="{{$bunk->date}}" class="form-control date-cell" disabled></td>
                                            <td class="text-center">
                                                <a href="" id="edit{{$bunk->id}}" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="update{{$bunk->id}}" class="mr-2 d-none"><i class="fas fa-save text-blue font-16"></i></a>
                                                <a href="" id="clear{{$bunk->id}}" class="d-none"><i class="mdi mdi-close-box-outline text-warning font-16"></i></a>
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

            // Initialize DataTable with custom length menu and default page length
            $('#datatable').dataTable({
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                pageLength: -1
            });

            // Handle edit button click event
            $('body').on('click', '[id^=edit]', function (event) {
                event.preventDefault();
                let id = getIdFromElement(this, 'edit');
                resetData(`#amount${id}`, false);
                resetData(`#date${id}`, false);
                toggleEditMode(id, true);
                $(`#amount${id}`).focus();
            });

            // Handle update button click event
            $('body').on('click', '[id^=update]', function (event) {
                event.preventDefault();
                let id = getIdFromElement(this, 'update');
                let amount = $(`#amount${id}`).val();
                let date = $(`#date${id}`).val();

                // Validate input fields
                if (!amount && date) {
                    showAlert('Please Enter Amount');
                } else if (!date && amount) {
                    showAlert('Please Enter Date');
                } else {
                    updateTurnoverData(id, amount, date);
                }
            });

            // Handle clear button click event
            $('body').on('click', '[id^=clear]', function (event) {
                event.preventDefault();
                let id = getIdFromElement(this, 'clear');
                loadData(`#amount${id}`);
                loadData(`#date${id}`);
                toggleEditMode(id, false);
            });

            // Utility function to extract ID from element
            function getIdFromElement(element, prefix) {
                return $(element).attr('id').replace(prefix, '');
            }

            // Function to toggle edit mode UI
            function toggleEditMode(id, isEditing) {
                $(`#amount${id}, #date${id}`).prop('disabled', !isEditing);
                $(`#update${id}, #clear${id}`).toggleClass('d-none', !isEditing);
                $(`#edit${id}`).toggleClass('d-none', isEditing);
            }

            // Function to reset data value for an input
            function resetData(selector, disabled = true) {
                let element = $(selector);
                element.data('value', element.val());
                element.attr('data-value', element.val());
                element.prop('disabled', disabled);
            }

            // Function to load data value back into an input
            function loadData(selector) {
                let element = $(selector);
                element.val(element.data('value'));
            }

            // Function to update petrol bunk turnover data via AJAX
            function updateTurnoverData(id, amount, date) {
                $.ajax({
                    url: "{{ route('bunks.turnover.update') }}",
                    method: 'PATCH',
                    data: {
                        bunk_id : id,
                        amount  : amount,
                        date    : date,
                    },
                    dataType: "json"
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    let amt = Math.round($(`#amount${id}`).val());
                    $(`#amount${id}`).val(amt);
                    resetData(`#amount${id}`);
                    resetData(`#date${id}`);
                    toggleEditMode(id, false);
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            // Function to show a Swal alert with a custom message
            function showAlert(message) {
                Swal.fire('Attention', message, 'warning');
            }

            // Restrict input to numbers and a single decimal point
            $(".amount-cell").on("keydown", function (e) {
                let key = e.key;

                // Allow numbers, backspace, and one decimal point
                if (
                    !(
                        (key >= '0' && key <= '9') || // Allow numbers
                        key === '.' && !this.value.includes('.') || // Allow one decimal point
                        key === 'Backspace' || // Allow backspace
                        key === 'Tab' || // Allow tab navigation
                        key === 'ArrowLeft' || // Allow left arrow
                        key === 'ArrowRight' || // Allow right arrow
                        key === 'Delete' || // Allow delete
                        key === '-' // Allow minus sign
                    )
                ) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
