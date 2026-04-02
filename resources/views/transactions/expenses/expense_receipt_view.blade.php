@extends('app-layouts.admin-master')

@section('title', 'Expense Denomination')

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
                    @slot('title') Expense Denomination @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Denomination @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-9 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Order Info -->
                        <div class="px-2">
                            @if(count($denom) == 1)
                            @php
                                $singleDeno = $denom->first(); // Access the first record in the collection
                            @endphp
                            <div class="row my-2">                                
                                <div class="col-md-3">
                                    Date <br/>
                                    <div class="mt-2">Expense Name </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="my-bold blue-text">{{ getIndiaDate($singleDeno->expense_date) }}</div>
                                    <div class="mt-2">
                                        {{ $singleDeno->expense_name }}
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a> 
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount </div>
                                <div class="col-md-9">                                    
                                    {{ "Rs. ".$singleDeno->ExDeno->denomination_amount }}
                                </div>
                            </div>                                                    
                            <div class="row my-2">
                                <div class="col-md-3">Expense Status </div>
                                <div class="col-md-9">                                    
                                    {{ $singleDeno->expense_status }}
                                </div>
                            </div>                                                    
                            @else                                
                            <div class="row my-2">                                
                                <div class="col-md-3 col-sm-4">
                                    Date <br/>
                                    <div class="mt-2">Amount </div>
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="my-bold blue-text">{{ getIndiaDate($denom->first()->expense_date) }}</div>
                                    <div class="mt-1">{{ "Rs. ".$denom->first()->ExDeno->denomination_amount }}</div>                                        
                                </div>
                                <div class="col-md-4 col-sm-4 text-right">
                                    <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a> 
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <div class="table-responsive">
                                        <table id="deTable" class="table table-sm table-bordered nowrap text-right">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">S.No</th>
                                                    <th class="text-center">Expense Name</th>
                                                    <th class="text-center">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($denom as $deno)
                                                <tr>
                                                    <td class="text-center">{{ $loop->index + 1 }}</td>
                                                    <td class="text-left">{{ $deno->expense_name }}</td>
                                                    <td class="text-center">{{ $deno->expense_amount }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            {{-- <tfoot>
                                                <tr>
                                                <td colspan="2" class="text-right">Total</td>
                                                <td class="text-center">{{ $singleDeno->ExDeno->denomination_amount }}</td>
                                                </tr>
                                            </tfoot>                  --}}
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        <!-- receipt Table Table -->
                        <div id="receipt_denomination">
                        <h6 class="my-heading p-2 pt-1">Denomination :</h6>
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="table-responsive">
                                    <table id="denomTable" class="table table-sm table-bordered nowrap text-right">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">Note</th>
                                                <th class="text-center">Count</th>
                                                <th class="text-center">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notes as $note)                                            
                                                <tr>                                                                                                 
                                                    <td width="70px" style="border-right-width:0px"> 
                                                        {{ $note->note_value }} &ensp; X 
                                                    </td>
                                                    @php
                                                        $found = false;
                                                        $total = null;  // Default to 0 in case no match is found
                                                    @endphp
                                    
                                                    @foreach ($denomination as $deno)
                                                        @foreach($deno as $amount => $count)  <!-- Loop through the key-value pairs in each denomination -->
                                                            @if($amount == $note->note_value)  <!-- If there's a match -->
                                                                <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                                    {{ $count }} &ensp; = 
                                                                </td>
                                                                @php
                                                                    $found = true; 
                                                                    $total = $count * $note->note_value;  // Calculate total
                                                                @endphp
                                                            @endif
                                                        @endforeach
                                                    @endforeach
                                    
                                                    <!-- If no match found for the note_value, show 0 -->
                                                    @if(!$found)
                                                        <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                              &ensp; = 
                                                        </td>
                                                    @endif
                                                    <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt{{ $note->note_value }}">
                                                        {{ $total }}  <!-- Display total for the note -->
                                                    </td>                                                    
                                                </tr>
                                            @endforeach
                                    
                                            <!-- Handling the coins row -->
                                            <tr>
                                                <td width="70px" style="border-right-width:0px"> Coins </td>
                                                @php
                                                    $found = false;
                                                    $total = null;  // Default to 0 for coins as well
                                                @endphp 
                                                @foreach ($denomination as $deno)
                                                    @foreach($deno as $amount => $count)  <!-- Loop through the key-value pairs in each denomination -->
                                                        @if($amount == "1")  <!-- Handle coins specifically (1) -->
                                                            <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                                {{ $count }} &ensp; = 
                                                            </td>
                                                            @php
                                                                $found = true; 
                                                                $total = $count * $amount;  // Calculate coin total
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                    
                                                @if(!$found)
                                                    <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                          &ensp; = 
                                                    </td>
                                                @endif
                                                <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt1">
                                                    {{ $total }}  <!-- Display coin total -->
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th id="denomTotal" style="padding-right:20px">
                                                    {{ $denom->first()->ExDeno->denomination_amount }}  <!-- Display total denomination amount -->
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    
                                    
                                </div>
                            </div>
                        </div><!--end print-->
                        </div>                    
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
    var listId = @json($allIds);
    var currentNo = @json($ids);
    console.log(listId);
    console.log(currentNo);
    $(document).ready(function () {
        $(document).on('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                $('#btnPrev').click();
            }
            else if (event.key === 'ArrowRight') {
                $('#btnNext').click();
            }
        });  
        function getCurrentIndex(receiptNum) {
            return listId.findIndex(item => item == receiptNum);
        }
        $('#btnPrev').on("click", function () {
            var currentReceipt = currentNo[0];                   
            var currentIndex = getCurrentIndex(currentReceipt);
            console.log(currentIndex);
            if (currentIndex > 0) {
                var prevReceipt = listId[currentIndex -1];                
                showOrder([prevReceipt]); 
            } else {
                Swal.fire('Sorry!', 'No Previous Denomination!', 'warning'); 
            }
        });
        $('#btnNext').on("click", function () {
            var currentReceipt = currentNo[currentNo.length - 1];    
            console.log(currentReceipt);         
            var currentIndex = getCurrentIndex(currentReceipt); 
            console.log(currentIndex);

            if (currentIndex < listId.length-1) {                
                var nextReceipt = listId[currentIndex + 1];
                showOrder([nextReceipt]); // Show the next receipt
            } else {
                Swal.fire('Sorry!', 'No Next Denomination!', 'warning'); 
            }
        });

        // Function to show a specific receipt based on its ID
        function showOrder(receiptId) {
            console.log(receiptId);
            let form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("expense.receipt.view") }}'
                });

                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add receipt ID
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id',
                    'value': receiptId
                }));
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'entryDate',
                    'value': '{{ $date }}'  // Convert date to YYYY-MM-DD format using Carbon
                }));                
                // Append form to body and submit
                form.appendTo('body').submit();
        }
        $('#btnPrint').click(function () {
            if (!$('#receipt_denomination').length) {
                Swal.fire('Sorry!', 'No Data Found to Print', 'warning');
            } else {
                // Hide buttons before printing
                $('#btnPrint, #btnPrev, #btnNext').hide(); 
                
                // Save the original content to restore after printing
                var originalContents = $('body').html(); 

                // Prepare the HTML for printing
                var printContents = `
                    <div style="width: 210mm; height: 148mm; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; padding: 10mm; box-sizing: border-box;">
                        <style>
                            @media print {
                                body {
                                    margin: 0;
                                    padding: 0;
                                    background-color: #f4f4f4;
                                }

                                .voucher-container {
                                    width: 210mm;
                                    height: 148mm;
                                    page-break-after: avoid;
                                    margin: 0 auto;
                                    border: 1px solid #000;
                                    padding: 10mm;
                                    background-color: #fff;
                                    box-sizing: border-box;
                                    position: relative;
                                }

                                .header {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    padding: 10px;
                                    border-bottom: 3px solid #b87333;
                                }

                                .header-left h1 {
                                    font-size: 24px;
                                    color: #b87333;
                                    font-weight: bold;
                                    margin: 0;
                                    text-transform: uppercase;
                                }

                                .header-left h2 {
                                    font-size: 20px;
                                    color: #a83279; /* Updated color for "THE TASTE OF HAPPINESS" */
                                    font-weight: bold;
                                    margin: 0;
                                    text-transform: uppercase;
                                    font-style: italic;
                                }

                                .header-left p {
                                    font-size: 16px; /* Increased font size for address and phone */
                                    margin: 5px 0;
                                    color: #333;
                                }

                                .header-right {
                                    background-color: #b87333;
                                    color: #fff;
                                    padding: 5px 10px;
                                    border-radius: 5px;
                                    font-size: 14px;
                                    font-weight: bold;
                                }

                                .content {
                                    margin: 20px 0;
                                }

                                .content p {
                                    margin: 5px 0;
                                    font-size: 16px;
                                    color: #333;
                                }

                                .table-responsive {
                                    margin-top: 20px;
                                }

                                #deTable {
                                    width: 100%;
                                    border-collapse: collapse;
                                }

                                #deTable th {
                                    background-color: #000; /* Darker header color */
                                    color: white;
                                    text-align: center;
                                    padding: 10px;
                                    border: 1px solid #000; /* Dark border for header */
                                }

                                #deTable td {
                                    text-align: center;
                                    padding: 8px;
                                    border: 1px solid #222; /* Slightly darker border for cells */
                                }

                                .signatures {
                                    position: absolute;
                                    bottom: 20px;
                                    right: 20px;
                                    text-align: right;
                                }

                                .signatures p {
                                    margin: 0;
                                    font-size: 14px;
                                    font-weight: bold;
                                    color: #333;
                                }

                                .signatures .line {
                                    width: 120px;
                                    height: 1px;
                                    background-color: black;
                                    margin: 5px 0;
                                    margin-bottom: 10px;
                                }

                                .total-amount-box {
                                    margin-top: 20px;
                                    padding: 5px;
                                    border: 2px solid #333;
                                    border-radius: 5px;
                                    background-color: #f4f4f4;
                                    text-align: center;
                                }

                                .total-amount-box p {
                                    font-size: 18px;
                                    font-weight: bold;
                                    margin: 0;
                                    color: #333;
                                }
                            }
                        </style>
                        @php
                            $singleDeno = $denom->first(); // Access the first record in the collection
                        @endphp
                        <div class="voucher-container">
                            <div class="header">
                                <div class="header-left">
                                    <h2>THE TASTE OF HAPPINESS</h2>
                                    <h1>AASAII FOOD PRODUCT</h1>
                                    <p>14-A, Vaiyapuri Goundanar, Uppidamangalam PO, Karur-639114</p>
                                    <p>Phone: 9842089525</p>
                                </div>
                                <div class="header-right">Receipt</div>
                            </div>
                            <div class="content">
                                <!-- Date section added here -->
                                <p><strong>Date:</strong> {{ getIndiaDate($singleDeno->expense_date) }}</p>
                            </div>
                            <div class="table-responsive">
                                <table id="deTable" class="table table-sm table-bordered nowrap text-right">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-left">Expense Name</th>
                                            <th class="text-center">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($denom as $deno)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-left">{{ $deno->expense_name }}</td>
                                            <td class="text-center">{{ $deno->expense_amount }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>                                                
                                </table>
                            </div>
                            <!-- Total Amount Box below the table -->
                            <div class="total-amount-box">
                                <p><strong>Total Amount:</strong> Rs. {{ $singleDeno->ExDeno->denomination_amount }}</p>
                            </div>
                            <div class="signatures" style="text-align: right; margin-top: 50px; margin-bottom:20px; margin-right: 50px;">
                                <p>Signature</p>
                                <div class="line" style="width: 120px; height: 1px; background-color: black; margin: 5px 0;"></div>
                            </div>
                        </div>
                    </div>
                `;
                // Replace the body content with the print content
                $('body').html(printContents);
                
                // Trigger the print dialog
                window.print();
                
                // Restore the original content after printing
                $('body').html(originalContents);

                // Show the buttons again
                $('#btnPrint, #btnPrev, #btnNext').show();
            }
        });

    });

</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop