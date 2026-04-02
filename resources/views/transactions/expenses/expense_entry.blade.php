@extends('app-layouts.admin-master')

@section('title', 'Expense Entry')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.1/dist/sweetalert2.min.css" rel="stylesheet">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') {{ isset($expenseEntry) ? 'Edit Expense' : 'Add Expense' }} @endslot
                    @slot('item1') Transaction @endslot
                    @slot('item2') Expenses @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
        @php
            if (isset($expenseEntry)) {
                $expenseName  = $expenseEntry->expense_name;
                $narration    = $expenseEntry->expense_narration;
                $amount       = $expenseEntry->expense_amount;                
                $date         = $expenseEntry->expense_date;
            } else {
                $expenseName  = "";
                $narration    = "";
                $amount       = "";                                
                $date         = $date;
            }
        @endphp

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <!-- Check if we're editing or adding -->
                        <form method="POST" id="expenseForm" action="{{ isset($expenseEntry) ? route('expense.entry.update',['id'=> $id]) : route('expense.entry.store') }}">

                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <!-- Date Field -->
                                    <div class="form-group row">
                                        <div class="col-4">
                                            <label class="form-label" for="date">Date</label>
                                        </div>
                                        <div class="col-6">
                                            <input id="dateInput" class="form-control" type="date" name="date" value="{{ $date }}" min="2025-04-01" max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        </div>
                                    </div>
                        
                                    <!-- Expense Name Field -->
                                    <div class="form-group row">
                                        <div class="col-4">
                                            <label class="form-label" for="expenseName">Expense type</label>
                                        </div>
                                        <div class="col-6">
                                            <select class="form-control tdR" name="expenseName">
                                                <option value="">Select Expense</option> 
                                                @foreach ($expenses as $expense)      
                                                    <option value="{{$expense->name}}" {{ old('expenseName', $expenseName) == $expense->name ? 'selected' : '' }}>{{$expense->name}}</option>           
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                        
                                    <!-- Narration Field -->
                                    <div class="form-group row">
                                        <div class="col-4">
                                            <label class="form-label" for="narration">Narration</label>
                                        </div>
                                        <div class="col-6">
                                            <textarea class="form-control tdR" id="narration" style="height: 100px" name="narration">{{ old('narration', $narration) }}</textarea>
                                        </div>
                                    </div>
                        
                                    <!-- Amount Field -->
                                    <div class="form-group row">
                                        <div class="col-4">
                                            <label class="form-label" for="amount">Amount</label>
                                        </div>
                                        <div class="col-6">
                                            <input class="form-control tdR" id="expenseAmount" type="text" name="amount" value="{{ old('amount', $amount) }}">
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <button type="button" id="submitForm" class="btn btn-primary">{{ isset($expenseEntry) ? 'Update' : 'Submit' }}</button>
                                </div>
                            </div>
                        </form>                        
                    </div><!--end card-body-->                    
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.1/dist/sweetalert2.min.js"></script>       
    <script>        
        $(document).ready(function () {            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Submit the form and denomination data
            $('#submitForm').click(function (event) {   
                event.preventDefault();     
                const expenseData = $('#expenseForm').serializeArray();               
                const expenseAmount = $('#expenseAmount').val();
                const expenseName = $('select[name="expenseName"]').val();
                const narration = $('textarea[name="narration"]').val();

                if (!expenseName) {
                    Swal.fire('Error!', 'Please enter the expense name.', 'warning');
                    return;
                }

                if (!narration) {
                    Swal.fire('Error!', 'Please enter the expense narration.', 'warning');
                    return;
                }
                
                if (!expenseAmount) {
                    Swal.fire('Error!', 'Please enter the expense amount.', 'warning');
                    return;
                }             

                $.ajax({
                    url: $('#expenseForm').attr('action'),
                    type: 'POST',
                    data: expenseData,                    
                    success: function (response) {                        
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            timer: 3000,                    
                            willClose: () => {
                                // Optionally reset the form after success                                
                                // $('#expenseForm')[0].reset();
                                $('.tdR').text('');
                                $('.tdR').val('');     
                                const expenseDropdown = $('select[name="expenseName"]');
                                expenseDropdown.empty();                                 
                                expenseDropdown.append('<option value="">Select Expense</option>');                               
                                response.expenses.forEach(expense => {
                                    expenseDropdown.append(
                                        `<option value="${expense.name}">${expense.name}</option>`
                                    );
                                });                                
                            }
                        });
                    },
                    error: function (error) {
                        Swal.fire('Error!', error.responseText, 'error');
                    }
                });
            });
            $('#expenseForm').on('keydown', function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();  // Prevent default form submission
                    
                    let allFieldsFilled = true;
                    let firstEmptyField = null;
                    $(this).find('input, select, textarea').each(function() {
                        if ($(this).val() === "" || $(this).val() === null) {
                            if (firstEmptyField === null) {
                        firstEmptyField = $(this); 
                    }
                        allFieldsFilled = false; 
                        }
                    });
                    if (allFieldsFilled) {
                        $('#submitForm').click();  
                    } else {
                        firstEmptyField.focus();
                    }
                }
            });                                                                                                     

        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
