@extends('app-layouts.admin-master')

@section('title', 'Settings')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        label {            
            margin-left: 20px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Settings @endslot
                    @slot('item1') Masters @endslot                    
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title pb-2">Invoice Number Formats</h4>
                            <form>
                                <div class="form-group row" style="margin-bottom: 16px"> 
                                    <label for="sales_invoice" class="col-sm-4 col-form-label" style="text-align:left">Sales Invoice</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="sales_invoice" name="sales_invoice" value="{{ $settings[0]->value }}">
                                    </div>
                                </div>
                                <div class="form-group row" style="margin-bottom: 16px">
                                    <label for="tax_invoice" class="col-sm-4 col-form-label" style="text-align:left">Tax Invoice</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="tax_invoice" name="tax_invoice" value="{{ $settings[1]->value }}">
                                    </div>
                                </div>
                                <div class="form-group row" style="margin-bottom: 16px">
                                    <label for="bulk_milk" class="col-sm-4 col-form-label" style="text-align:left">Bulk Milk Invoice</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="bulk_milk" name="bulk_milk" value="{{ $settings[2]->value }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="conversion" class="col-sm-4 col-form-label" style="text-align:left">Conversion</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="conversion" name="conversion" value="{{ $settings[3]->value }}">
                                    </div>
                                </div>
                                <div class="form-group row" style="margin-bottom: 16px">
                                    <label for="order" class="col-sm-4 col-form-label" style="text-align:left">Orders</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="order" name="order" value="{{ $settings[4]->value }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-10" style="text-align:right">                                        
                                        <a id="edit_inv_formats" href="#" class="btn btn-gradient-danger px-3 mr-2"><i class="fas fa-edit"></i></a>
                                        <a id="save_inv_formats" href="#" class="btn btn-primary px-3">Save</a>
                                    </div>
                                </div>
                            </form>                    
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->                        
        </div><!--end row--> 
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>     
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            $(window).on('load',function () {
                $("#sales_invoice").prop('disabled', true);
                $("#tax_invoice").prop('disabled', true);
                $("#bulk_milk").prop('disabled', true);
                $("#conversion").prop('disabled', true);
                $("#order").prop('disabled', true);
            });

            $('#edit_inv_formats').click(function (event) {
                event.preventDefault();
                $("#sales_invoice").prop('disabled', false);
                $("#tax_invoice").prop('disabled', false);
                $("#bulk_milk").prop('disabled', false);
                $("#conversion").prop('disabled', false);
                $("#order").prop('disabled', false);
            });

            $('#save_inv_formats').click(function (event) {
                event.preventDefault();

                if($('#sales_invoice').is(':disabled')){
                    Swal.fire('Attention!','Please edit info to make changes','info');
                    return;
                }
                
                var salesInvoice = $("#sales_invoice").val();
                var taxInvoice   = $("#tax_invoice").val();    
                var bulkMilk     = $("#bulk_milk").val();    
                var conversion   = $("#conversion").val();
                var order        = $("#order").val();

                if(salesInvoice == "") {
                    Swal.fire('Sorry!','Please Enter Sales Invoice Format','error');
                }
                else if(taxInvoice == "") {
                    Swal.fire('Sorry!','Please Enter Tax Invoice Format','error');
                }
                else if(bulkMilk == "") {
                    Swal.fire('Sorry!','Please Enter Bulk Milk Invoice Format','error');
                }
                else if(conversion == "") {
                    Swal.fire('Sorry!','Please Enter Conversion Format','error');
                }
                else if(order == "") {
                    Swal.fire('Sorry!','Please Enter Order Format','error');
                }
                else {
                    $.ajax({
                        url: '/settings/update',
                        type: "POST",
                        data: {
                            category      : 'Invoice',
                            sales_invoice : salesInvoice,
                            tax_invoice   : taxInvoice,
                            bulk_milk     : bulkMilk,
                            conversion    : conversion,
                            order         : order
                        },
                        dataType: 'json',
                        success: function (data) {               
                            $("#sales_invoice").prop('disabled', false);
                            $("#tax_invoice").prop('disabled', false);
                            $("#bulk_milk").prop('disabled', false);
                            $("#conversion").prop('disabled', false);
                            $("#order").prop('disabled', false);
                            Swal.fire({
                                    title:'Success!',
                                    text: "Invoice Formats Updated Successfully",
                                    type:'success'
                                }
                            )
                            .then(
                                function() { 
                                    window.location.reload(true);
                                }
                            );  
                        },
                        error: function (data, textStatus, errorThrown) {                            
                            Swal.fire({
                                    title:'Sorry!',
                                    text: data.responseText,
                                    type:'warning'
                                }
                            );
                        }
                    });
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
