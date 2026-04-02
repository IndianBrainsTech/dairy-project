@extends('app-layouts.admin-master')

@section('title', 'GST Master')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') GST Master @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Taxation @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">GST Master</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_gst" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_gst"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add GST Data</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>HSN Code</th>
                                    <th>Description</th>
                                    <th>GST</th>
                                    <th>SGST</th>
                                    <th>CGST</th>
                                    <th>IGST</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($gst_masters as $gst_data)
                                        @php
                                            $percent = "%";
                                            if($gst_data->tax_type == "Exempted")
                                                $percent = "";
                                        @endphp
                                        <tr>                                                
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $gst_data->hsn_code }}</td>
                                            <td>{{ $gst_data->description }}</td>
                                            <td>{{ $gst_data->gst }} {{ $percent }}</td>
                                            <td>{{ $gst_data->sgst }} {{ $percent }}</td>
                                            <td>{{ $gst_data->cgst }} {{ $percent }}</td>
                                            <td>{{ $gst_data->igst }} {{ $percent }}</td>
                                            <td>                                                       
                                                <a href="" id="edit_gst" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_tax" data-id="{{ $gst_data->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_gst" data-id="{{ $gst_data->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of Tax Modal -->
    <div class="modal fade" id="modal_gst" tabindex="-1" role="dialog" aria-labelledby="modalTaxLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_gst_title">Add GST Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_gst">
                    <input type="hidden" id="gst_id" name="gst_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="hsn_code" class="col-sm-4 col-form-label">HSN Code <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="hsn_code" required="" name="hsn_code" maxlength="8">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="description" class="col-sm-4 col-form-label">Description</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="description" name="description" maxlength="50">                                        
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="tax_type" class="col-sm-4 col-form-label" style="margin-top:-4px">Tax Type <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <div class="form-check-inline my-1">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="rdo_taxable" name="tax_type" class="custom-control-input" checked="" data-toggle="collapse" data-target="#collapseTaxData" aria-expanded="false" aria-controls="collapseTaxData">
                                                <label class="custom-control-label" for="rdo_taxable">Taxable</label>
                                            </div>
                                        </div>
                                        <div class="form-check-inline my-1">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="rdo_exempted" name="tax_type" class="custom-control-input"  data-toggle="collapse" data-target="#collapseTaxData" aria-expanded="false" aria-controls="collapseTaxData">
                                                <label class="custom-control-label" for="rdo_exempted">Exempted</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="collapse show" id="collapseTaxData" style="margin-top:-20px; margin-bottom:-10px">
                                    <div class="card mb-0 card-body" style="border:1px solid lightGray">
                                        <div class="form-group row">                                    
                                            <label for="gst" class="col-sm-2 col-form-label">GST <small class="text-danger font-13">*</small></label>
                                            <div class="col-sm-4 input-group">                                    
                                                <input type="number" class="form-control" id="gst" required="" name="gst" min="0" max="50">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                                                            
                                            <label for="igst" class="col-sm-2 col-form-label">IGST <small class="text-danger font-13">*</small></label>
                                            <div class="col-sm-4 input-group">                                    
                                                <input type="number" class="form-control" id="igst" required="" name="igst" min="0" max="50" tabindex="-1" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="form-group row" style="margin-bottom:5px">                                    
                                            <label for="sgst" class="col-sm-2 col-form-label">SGST <small class="text-danger font-13">*</small></label>
                                            <div class="col-sm-4 input-group">                                    
                                                <input type="number" class="form-control" id="sgst" required="" name="sgst" min="0" max="50" step="0.5">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                                                                
                                            <label for="cgst" class="col-sm-2 col-form-label">CGST <small class="text-danger font-13">*</small></label>
                                            <div class="col-sm-4 input-group">                                    
                                                <input type="number" class="form-control" id="cgst" required="" name="cgst" min="0" max="50" step="0.5">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add GST Data"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Tax Modal -->  
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
            
            $('body').on('click', '#add_gst', function (event) {
                event.preventDefault();                
                $('#modal_gst_title').html("Add Tax Data");
                $('#gst_id').val("");                
                $('#hsn_code').val("");                
                $('#description').val("");
                $('#gst').val("");
                $('#sgst').val("");
                $('#cgst').val("");
                $('#igst').val("");
                $('#submit').val("Add GST Data");
                $('#modal_gst').modal('show');
            });

            $('body').on('click', '#edit_gst', function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                $.get('/gst_master/' + id, function (data) {
                    $('#modal_gst_title').html("Edit GST Data");
                    $('#gst_id').val(data.gst_master.id);
                    $('#hsn_code').val(data.gst_master.hsn_code);
                    $('#description').val(data.gst_master.description);
                    $('#submit').val("Update");
                    $('#modal_gst').modal('show');

                    var tax_type = data.gst_master.tax_type;
                    if(tax_type == "Taxable") {
                        $("#rdo_taxable").prop("checked", true);     
                        if($("#collapseTaxData").is(":hidden")){
                            $("#collapseTaxData").show();
                        }
                        $('#gst').val(data.gst_master.gst);
                        $('#sgst').val(data.gst_master.sgst);
                        $('#cgst').val(data.gst_master.cgst);
                        $('#igst').val(data.gst_master.igst);
                    }
                    else {
                        $("#rdo_exempted").prop("checked", true);     
                        if($("#collapseTaxData").is(":visible")){
                            $("#collapseTaxData").hide();
                        }   
                        $('#gst').val('');
                        $('#sgst').val('');
                        $('#cgst').val('');
                        $('#igst').val('');
                    }

                })
            });

            $('body').on('click', '#delete_gst', function (event) {
                event.preventDefault();  
                var id = $(this).data('id');                   
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '$success',
                    cancelButtonColor: '$danger',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then(function(result) {                    
                    if (result.value) {
                        $.ajax({
                            url:'/gst_master/' + id,
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','GST Data has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        }) 
                    }
                })
            });
            
            $('#rdo_taxable').click(function () {
                $("#collapseTaxData").show();
            });

            $('#rdo_exempted').click(function () {
                $("#collapseTaxData").hide();
            });

            $('#gst').focusout(function () {
                var gst = $("#gst").val();
                if(gst>=0 && gst<=50) {
                    $("#igst").val(gst);
                    $("#sgst").val(gst/2);
                    $("#cgst").val(gst/2);
                }
                else {
                    Swal.fire('Error','Incorrect Value Entered for GST','error');
                }
            });

            function isValid(hsn_code,gst,sgst,cgst) {
                let valid = false;
                let isnum = /^\d+$/.test(hsn_code);
                let is_taxable = $("#rdo_taxable").is(":checked");
                if(!hsn_code) {
                    Swal.fire('Attention','Please Enter HSN Code','error');
                }
                else if(!isnum) {
                    Swal.fire('Attention','HSN Code must have only digits','error');
                }
                else if((hsn_code+'').length<4) {
                    Swal.fire('Attention','HSN Code should have atleast 4 digits','error');
                }
                else if(is_taxable) {
                    if(!gst)
                        Swal.fire('Attention','Please Enter GST Value','error');
                    else if(!sgst)
                        Swal.fire('Attention','Please Enter SGST Value','error');
                    else if(!cgst)
                        Swal.fire('Attention','Please Enter CGST Value','error');
                    else if(gst<0 || gst>50)
                        Swal.fire('Error','Incorrect Value Entered for GST','error');
                    else if(sgst<0 || sgst>gst)
                        Swal.fire('Error','Incorrect Value Entered for SGST','error');
                    else if(cgst<0 || cgst>gst)
                        Swal.fire('Error','Incorrect Value Entered for CGST','error');
                    else if(sgst+cgst!=gst)
                        Swal.fire('Error','SGST/CGST values not tally with GST','error');
                    else
                        valid = true;
                }
                else {
                    valid = true;
                }
                return valid;
            }

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();   
                
                var id = $("#gst_id").val();
                var hsn_code = $("#hsn_code").val();
                var description = $("#description").val();
                var gst = $("#gst").val();
                var sgst = $("#sgst").val();
                var cgst = $("#cgst").val();
                var igst = $("#igst").val();
                var successText = "GST Data has been updated!";

                gst = parseFloat(gst);
                sgst = parseFloat(sgst);
                cgst = parseFloat(cgst);
                igst = parseFloat(igst);

                if(isValid(hsn_code,gst,sgst,cgst)) {

                    if(!id) {
                        id = "0";
                        successText = "GST Data has been added!";
                    }

                    var tax_type = "Taxable";
                    var is_exempted = $("#rdo_exempted").is(":checked");
                    if(is_exempted) {
                        tax_type = "Exempted";
                        gst = '';
                        sgst = '';
                        cgst = '';
                        igst = '';
                    }

                    $.ajax({
                        url: '/gst_master/' + id,
                        type: "POST",
                        data: {
                            id: id,
                            hsn_code: hsn_code,                            
                            description: description,
                            tax_type: tax_type,
                            gst: gst,
                            sgst: sgst,
                            cgst: cgst,
                            igst: igst
                        },
                        dataType: 'json',
                        success: function (data) {               
                            $('#form_gst').trigger("reset");
                            $('#modal_gst').modal('hide');                                                
                            Swal.fire({
                                    title:'Success!',
                                    text:successText,
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
                            // var errorText = data.responseText;
                            var errorText = "An Error Occurred";
                            if(data.responseText.indexOf("Duplicate entry") !== -1) {                            
                                errorText = "HSN Already Exists";
                            }
                            Swal.fire({
                                    title:'Sorry!',
                                    text:errorText,
                                    type:'warning',
                                    // confirmButtonColor: '$danger'
                                    confirmButtonColor: '#FF0000'
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
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>  
    <script src="{{ asset('assets/js/jquery.core.js') }}"></script>
@stop
