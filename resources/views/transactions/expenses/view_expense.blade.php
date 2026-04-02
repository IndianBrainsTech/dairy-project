@extends('app-layouts.admin-master')

@section('title', 'View Expense')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Expense @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Expenses @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <!-- Order Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3 col-sm-3">
                                    Expense Date <br/>
                                    <div class="mt-2">Expense Name</div>
                                </div>
                                <div class="col-md-5 col-sm-5">
                                    <div class="my-bold blue-text">{{ getIndiaDate($expenseEntry->expense_date) }}</div>
                                    <div class="mt-2">{{$expenseEntry->expense_name}}</div>
                                </div>
                                <div class="col-md-4 col-sm-4 text-right">
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>                           
                            <div class="row my-2">
                                <div class="col-md-3 col-sm-3">Expense Narration</div>
                                <div class="col-md-9 col-sm-9">{{$expenseEntry->expense_narration}}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3 col-sm-3">Expense Amount</div>
                                <div class="col-md-9 col-sm-9">{{$expenseEntry->expense_amount}}</div>
                            </div>
                            <div class="row my-2">
                                @php
                                    function getStatusClass($status) {
                                        switch($status) {
                                            case 'Accepted':
                                                return 'primary';    
                                            case 'Rejected':
                                                return 'danger';   
                                            case 'Pending':
                                                return 'warning';   
                                            default:
                                                return 'secondary';   
                                        }
                                    }
                                @endphp
                                <div class="col-md-3 col-sm-3">Expense Status</div>
                                <div class="col-md-9 col-sm-9">
                                    <span class="p-1 badge badge-{{ getStatusClass($expenseEntry->expense_status) }}">
                                        {{ $expenseEntry->expense_status}}
                                    </span>
                                </div>
                                
                            </div>
                        </div>                                                             
                            <hr/>                            
                            @if($date == date('Y-m-d') && ($expenseEntry->expense_status == "Pending" || $expenseEntry->expense_status == "Created"))                           
                            <div class="text-center">                                
                                <a href="#" class="show" data-order="{{$expenseEntry->id}}"><button class="btn btn-warning btn-sm"> Edit Entry </button></a>
                            </div>
                            @endif
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>   
   
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
    var existingIds = @json($expensesId);  
    var currentId   = @json($id); 
    var entryDate   = @json($date);
    console.log(existingIds);   
    $(document).ready(function () {           
        var entryDate = "{{ $date }}";

        $(document).on('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                $('#btnPrev').click();
            }
            else if (event.key === 'ArrowRight') {
                $('#btnNext').click();
            }
        });     
               
        function getCurrentIndex(currentId) {
            return existingIds.findIndex(item => item == currentId);
        }
        $('#btnPrev').on("click", function () {           
            console.log('current_id'+ currentId);          
            var currentIndex = getCurrentIndex(currentId);
            console.log("cirrent index:" +currentIndex);
            if (currentIndex > 0) {
                var prevId = existingIds[currentIndex -1];                
                showEntry(prevId,entryDate); 
            } else {
                Swal.fire('Sorry!', 'No Previous Expense!', 'warning'); 
            }
        });
        $('#btnNext').on("click", function () {                
            var currentIndex = getCurrentIndex(currentId);             
            if (currentIndex < existingIds.length-1) {                
                var nextId = existingIds[currentIndex + 1];
                showEntry(nextId,entryDate); // Show the next receipt
            } else {
                Swal.fire('Sorry!', 'No Next Expense!', 'warning'); 
            }
        });

        function showEntry(Id, entryDate) {            
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('expense.entry.view') }}"  
            });           
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': csrfToken
            }));           
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'id',
                'value': Id
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'entryDate',
                'value': entryDate
            }));    
    
            // Append the form and submit it
            $('body').append(form);
            form.submit();
        }

        $('body').on('click', '.show', function (event) {
                var entry_id = $(this).attr('data-order');                
                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('expense.entry.edit') }}"
                });
 
                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add the data as hidden inputs
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id',
                    'value': entry_id
                }));              
                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });
   });

</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop