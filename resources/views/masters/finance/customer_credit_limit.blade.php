@extends('app-layouts.admin-master')

@section('title', 'Credit Limit')

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
                    @slot('title') Credit Limit  @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Openings @endslot                    
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th>Customer</th>
                                        <th class="text-right">Amount</th>
                                        {{-- <th class="text-center">As per Date</th> --}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td id="name{{$customer->id}}">{{ $customer->customer_name }}</td>
                                            <td style="width:120px"><input type="text" id="amount{{$customer->id}}" value="{{$customer->credit_limit}}" data-value="{{$customer->credit_limit}}" class="form-control amount-cell" maxlength="12" disabled></td>
                                            {{-- <td style="width:145px"><input type="date" id="date{{$customer->id}}" value="{{$customer->date}}" data-value="{{$customer->date}}" class="form-control date-cell" disabled></td> --}}
                                            <td class="text-center">
                                                <a href="" id="edit{{$customer->id}}" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="update{{$customer->id}}" class="mr-2 d-none"><i class="fas fa-save text-blue font-16"></i></a>
                                                <a href="" id="clear{{$customer->id}}" class="d-none"><i class="mdi mdi-close-box-outline text-warning font-16"></i></a>
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
    <script>
        $(document).ready(function()
        {
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
                console.log('Edit button clicked for ID: ' + id); 
                resetData(`#amount${id}`, false);
                toggleEditMode(id, true);
                $(`#amount${id}`).focus();
            });
            var oustd = null;
            // Handle update button click event
            $('body').on('click', '[id^=update]', function (event) {
                event.preventDefault();
                let id = getIdFromElement(this, 'update');                
                let amount = $(`#amount${id}`).val();               
                console.log("Total amount: " + amount);
                
                let url = "{{ route('receipts.receivables', ['customerId' => '__ID__']) }}".replace('__ID__', id);
                $.get(url, function(data) {                    
                    let invoices = data.invoices;
                    oustd = 0;
                    invoices.forEach(function(item) {                        
                        oustd += item.outstanding;
                    });     
                    
                    console.log("Total Outstanding: " + oustd);

                    // Move the condition checks inside the callback
                    if (amount == 0 && oustd != 0 && amount != '') {
                        showAlert(`To set the credit limit to 0, please clear the outstanding amount first.<br> 
                        <strong>Outstanding Amount:</strong> ${oustd}`); 
                    }  
                    else if (amount != '' && amount < oustd) {
                        showAlert(`Credit limit must always be greater than the outstanding amount.<br> 
                        <strong>Outstanding Amount:</strong> ${oustd}`);
                    }  
                    else {
                        updateCustomerData(id, amount);
                    } 
                }).fail(function() {
                    console.error("Failed to fetch receivables");
                });
            });


            // Handle clear button click event
            $('body').on('click', '[id^=clear]', function (event) {
                event.preventDefault();
                let id = getIdFromElement(this, 'clear');
                loadData(`#amount${id}`);
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
                element.prop('disabled', disabled);
            }

            // Function to load data value back into an input
            function loadData(selector) {
                let element = $(selector);
                element.val(element.data('value'));
            }

            // Function to update customer data via AJAX
            function updateCustomerData(id, amount) {
                let name = $(`#name${id}`).text();
                $.ajax({
                    url: "{{ route('customers.credit.limit') }}",
                    type: "POST",
                    data: {
                        cust_id: id,
                        name: name,
                        amount: amount,
                    },
                    dataType: 'json',
                    success: function (data) {
                        resetData(`#amount${id}`);
                        console.log(data);
                        console.log("Customer Data Updated!");
                        toggleEditMode(id, false);
                    },
                    error: function (data) {
                        showAlert(data.responseText);
                        console.log(data);
                    }
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
                        key === 'Delete' // Allow delete
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
