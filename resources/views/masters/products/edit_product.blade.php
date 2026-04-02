@extends('app-layouts.admin-master')

@section('title', 'Product')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">                
                @component('app-components.breadcrumb-4')
                    @slot('title') Edit Product @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Products @endslot
                    @slot('item3') Products @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">

                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            {{ Session::get('success') }}
                            @php ($isSuccess="yes") @endphp
                        </div>
                    @else
                        @php ($isSuccess="no") @endphp
                    @endif

                    @php
                        $prim_unit_id = "";
                        $prim_price = "";                        
                        foreach($productUnits as $prodUnit) {
                            if($prodUnit->prim_unit == 1) {
                                $prim_unit_id = $prodUnit->unit_id;
                                $prim_price = $prodUnit->price;
                            }
                        }
                    @endphp

                    <div class="card-body">                    
                        <h4 class="header-title mt-0 mb-3">Product Information</h4> <hr/>
                        <form id="frm_product" class="mb-0" method="post" action="{{ route('products.update', ['id' => $product->id]) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product Name <small class="text-danger font-13">*</small></label>
                                        <input type="text" value="{{ $product->name }}" class="form-control" id="prod_name" name="prod_name" required="" maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Short Name <small class="text-danger font-13">*</small></label>
                                        <input type="text" value="{{ $product->short_name }}" class="form-control" id="short_name" name="short_name" required="" maxlength="15">
                                    </div>
                                </div><!--end col-->                                                
                            </div><!--end row-->
                            <div class="row">
                                <div class="col-md-6">                            
                                    <div class="form-group">
                                        <label>Product Group <small class="text-danger font-13">*</small></label>
                                        <select class="form-control" id="prod_group" name="prod_group" required="">
                                            <option value="">Select</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}" @selected($group->id == $product->group_id)>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div><!--end col-->
                                <div class="col-md-6">                            
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" rows="3" id="prod_desc" name="prod_desc" placeholder="writing here..">{{ $product->description }}</textarea>
                                    </div>
                                </div><!--end col-->
                            </div><!--end row-->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">                                        
                                        <label>MRP <small class="text-danger font-13">*</small></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                            </div>
                                            <input type="number" value="{{ $product->mrp }}" class="form-control" id="mrp" name="mrp" required="" step="any" min="0">
                                        </div>
                                    </div>
                                </div><!--end col-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>FAT</label>
                                        <input type="number" value="{{ $product->fat }}" class="form-control" id="fat" name="fat" step="any" min="0">
                                    </div>
                                </div> <!--end col-->
                                <div class="col-md-4">
                                <div class="form-group">
                                        <label>SNF</label>
                                        <input type="number" value="{{ $product->snf }}" class="form-control" id="snf" name="snf" step="any" min="0">
                                    </div>           
                                </div> <!--end col-->                                               
                            </div><!--end row-->
                            <hr/>

                            <h4 class="header-title mt-0 mb-3">Tax Data</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hsn_code">HSN Code <small class="text-danger font-13">*</small></label>
                                        <select class="form-control" id="hsn_code" name="hsn_code" required="">
                                            <option value="">Select</option>
                                            @foreach($hsn_codes as $hsnCode)
                                                <option value="{{ $hsnCode->hsn_code }}" @selected($hsnCode->hsn_code == $product->hsn_code)>{{ $hsnCode->hsn_code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div><!--end col-->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax_type">Tax Type <small class="text-danger font-13">*</small></label>
                                        <div class="col-sm-12">
                                            <div class="form-check-inline my-1">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="rdo_taxable" name="tax_type" value="Taxable" class="custom-control-input" data-toggle="collapse" data-target="#collapseTaxData" aria-expanded="false" aria-controls="collapseTaxData" {{ $product->tax_type == "Taxable" ? "checked" : "" }}>
                                                    <label class="custom-control-label" for="rdo_taxable">Taxable</label>
                                                </div>
                                            </div>
                                            <div class="form-check-inline my-1">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="rdo_exempted" name="tax_type" value="Exempted" class="custom-control-input" data-toggle="collapse" data-target="#collapseTaxData" aria-expanded="false" aria-controls="collapseTaxData" {{ $product->tax_type == "Exempted" ? "checked" : "" }}>
                                                    <label class="custom-control-label" for="rdo_exempted">Exempted</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--end col-->
                            </div><!--end row-->

                            <div class="collapse {{ $product->tax_type == 'Taxable' ? 'show' : 'hide' }}" id="collapseTaxData">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="gst">GST <small class="text-danger font-13">*</small></label>
                                            <div class="input-group">
                                                <input type="number" value="{{ $product->gst }}" class="form-control" id="gst" name="gst" min="0" max="50">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!--end col-->

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sgst">SGST <small class="text-danger font-13">*</small></label>
                                            <div class="input-group">
                                                <input type="number" value="{{ $product->sgst }}" class="form-control" id="sgst" name="sgst" min="0" max="50" step="0.50">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!--end col-->

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="cgst">CGST <small class="text-danger font-13">*</small></label>
                                            <div class="input-group">
                                                <input type="number" value="{{ $product->cgst }}" class="form-control" id="cgst" name="cgst" min="0" max="50" step="0.50">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!--end col-->

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="igst">IGST <small class="text-danger font-13">*</small></label>
                                            <div class="input-group">
                                                <input type="number" value="{{ $product->igst }}" class="form-control" id="igst" required="" name="igst" min="0" max="50" tabindex="-1" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div><!--end div collapse-->
                            <hr/>

                            <h4 class="header-title mt-0 mb-3">Unit of Measurements</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Primary Unit <small class="text-danger font-13">*</small></label>
                                        <select class="form-control" id="select_primary_unit" name="select_primary_unit" required="">
                                            <option value="">Select</option>
                                            @foreach($units as $unit)                                                                                                
                                                <option value="{{ $unit->id }},{{ $unit->display_name }}" @selected($unit->id == $prim_unit_id)>{{ $unit->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Price <small class="text-danger font-13">*</small></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                            </div>
                                            <input type="number" value="{{ $prim_price }}" class="form-control" id="primary_price" name="primary_price" step="any" min="0" required="">
                                        </div>                                        
                                    </div>
                                </div><!--end col-->                                                
                            </div><!--end row-->

                            <div class="row">          
                            <div class="col-md-12">                   
                                <fieldset>
                                    <div class="myrepeater">

                                        <div data-repeater-list="units">                                        
                                            <div data-repeater-item="" style="display:none;">
                                                <div class="form-group row d-flex align-items-end">
                                                    
                                                    <div class="col-sm-4">
                                                        <label class="control-label">Additional Unit <small class="text-danger font-13">*</small></label>
                                                        <select name="unit_id" class="form-control addi_unit">                                                               
                                                            @foreach($units as $unit)
                                                                <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div><!--end col-->

                                                    <div class="col-sm-3">
                                                        <label class="control-label">Price <small class="text-danger font-13">*</small></label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                                            </div>
                                                            <input type="text" class="form-control" name="price" required="" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                                                        </div>                                                        
                                                    </div><!--end col-->

                                                    <div class="col-sm-5">
                                                        <label class="control-label">Conversion to Primary <small class="text-danger font-13">*</small></label>
                                                        <div class="row">
                                                            <div class="col-sm-8 input-group">                                                                
                                                                <input type="text" class="form-control" name="conversion" required="" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text unit_short"></span>
                                                                </div>
                                                            </div>    
                                                            <div class="col-sm-4">
                                                                <span data-repeater-delete="" class="btn btn-gradient-danger btn-sm" style="position:relative;top:50%;transform:translateY(-50%)">
                                                                    <span class="far fa-trash-alt mr-1"></span>Del
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div><!--end col-->
                                        
                                                </div><!--end row-->
                                            </div><!--end /div-->                                        
                                        </div><!--end repet-list-->

                                        <div class="form-group mb-0 row">
                                            <div class="col-sm-12">
                                                <span data-repeater-create="" class="btn btn-gradient-secondary">
                                                    <span class="fas fa-plus"></span> Add
                                                </span>                                                
                                            </div><!--end col-->
                                        </div><!--end row--> 

                                    </div> <!--end repeter-->                                           
                                </fieldset><!--end fieldset-->                                               
                            
                            </div><!--end row-->
                            </div>
                            <hr/>

                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-group">                                                 
                                                <h4 class="header-title mt-0 mb-3" style="margin-top:50px">Visibility Control</h4>
                                                <table width="100%">
                                                    <tr>
                                                        <td width="50%">
                                                            <label style="margin-bottom:0px">Mobile App</label>
                                                        </td>
                                                        <td>
                                                            <div class="custom-control custom-switch switch-pink">                                                    
                                                                <input type="checkbox" class="custom-control-input" id="customSwitch1" name="customSwitch1" @checked($product->visible_app)>
                                                                <label class="custom-control-label" for="customSwitch1" id="labelCustomSwitch1">{{$product->visible_app ? "ON" : "OFF"}}</label>
                                                            </div>   
                                                        </td>
                                                    </tr>

                                                    <tr height="50px">
                                                        <td>
                                                            <label style="margin-bottom:0px">Regular Invoice</label>
                                                        </td>
                                                        <td>
                                                            <div class="custom-control custom-switch switch-pink">                                                    
                                                                <input type="checkbox" class="custom-control-input" id="customSwitch2" name="customSwitch2" @checked($product->visible_invoice)>
                                                                <label class="custom-control-label" for="customSwitch2" id="labelCustomSwitch2">{{$product->visible_invoice ? "ON" : "OFF"}}</label>
                                                            </div>   
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label style="margin-bottom:0px">Bulk Milk Invoice</label>
                                                        </td>
                                                        <td>
                                                            <div class="custom-control custom-switch switch-pink">
                                                                <input type="checkbox" class="custom-control-input" id="customSwitch3" name="customSwitch3" @checked($product->visible_bulkmilk)>
                                                                <label class="custom-control-label" for="customSwitch3" id="labelCustomSwitch3">{{$product->visible_bulkmilk ? "ON" : "OFF"}}</label>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                </table>
                                            </div>                                             
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div><!--end col-->

                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-body">                                            
                                            <div class="form-group" style="margin-top:110px">
                                                <input type="reset"  id="reset" value="Clear" class="btn btn-gradient-danger btn-sm text-light px-4 mt-3 float-right mb-0 ml-2" />
                                                <input type="submit" id="submit" value="Save"  class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" />                
                                            </div>                                             
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div><!--end col-->
                            </div><!--end row-->

                            @if(Session::has('error'))
                                <div class="alert alert-danger">
                                    {{ Session::get('error') }}                            
                                </div>
                            @endif

                        </form><!--end form-->
                    </div><!--end card-body-->
                    
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            var isSuccess = '<?php echo $isSuccess; ?>';
            if(isSuccess == "yes") {
                Swal.fire({
                                title:'Success!',
                                text:"Product Updated Successfully",
                                type:'success'
                            }
                        )
                        .then(
                            function() { 
                                window.location.replace("{{ route('products.index') }}");
                            }
                        ); 
            }

            $('#hsn_code').change(function () {
                var hsn = $(this).val();
                if(hsn) {
                    $.get('/gst_info/' + hsn, function (data) {
                        var tax = data.gst_info;
                        if(tax.tax_type == "Taxable") {
                            $("#rdo_taxable").prop("checked", true);
                            if($("#collapseTaxData").is(":hidden")){
                                $("#collapseTaxData").show();
                            }
                            $('#gst').val(tax.gst);
                            $('#sgst').val(tax.sgst);
                            $('#cgst').val(tax.cgst);
                            $('#igst').val(tax.igst);
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
                }
            });

            $('#rdo_taxable').click(function () {
                if($("#rdo_taxable").is(":checked"))
                    $("#collapseTaxData").show();
            });

            $('#rdo_exempted').click(function () {
                if($("#rdo_exempted").is(":checked"))
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

            $('#select_primary_unit').change(function () {
                $('[data-repeater-list]').empty();                                   
            });
            
            var repeater = $('.myrepeater').repeater({
                initEmpty: true,
                show: function () {    
                    if(addRepeaterRow())
                        $(this).slideDown();
                },
                hide: function (remove) {                
                    $(this).slideUp(remove);                
                }
            });

            var list = [];
            @foreach($productUnits as $prodUnit)
                @if($prodUnit->prim_unit == 0)
                    list.push({'unit_id':'{{$prodUnit->unit_id}}','price':'{{$prodUnit->price}}','conversion':'{{$prodUnit->conversion}}'});
                @endif    
            @endforeach            
            repeater.setList(list);

            function addRepeaterRow() {
                // $('#select_primary_unit').trigger('change');
                var isAdd = false;                  
                var unit = $('#select_primary_unit').val();
                if(unit==="") {
                    Swal.fire('Attention','Please Select Primary Unit then Add Additional Unit','error');                    
                }
                else {                        
                    var i = unit.split(',')[0]; 
                    unit = unit.split(',')[1];                
                    $(".unit_short").html(unit);

                    var objects = document.getElementsByClassName("addi_unit");                    
                    var n = (objects.length-1);
                    var object = objects[n];                                                                
                    $(object).children(`option[value=${i}]`).remove();

                    for (i=0; i<n; i++) {
                    var sel = $(objects[i]).val();
                        $(object).children(`option[value=${sel}]`).remove();
                    }

                    if(($(object).children().length)==0)                        
                        Swal.fire('Attention','Sorry! No other unit found to add','error');
                    else
                        isAdd = true;
                }
                return isAdd;
            }

            $('#customSwitch1').change(function () {
                if(this.checked) 
                    $("#labelCustomSwitch1").html('ON');
                else
                    $("#labelCustomSwitch1").html('OFF');
            });

            $('#customSwitch2').change(function () {
                if(this.checked) 
                    $("#labelCustomSwitch2").html('ON');
                else
                    $("#labelCustomSwitch2").html('OFF');
            });

            $('#customSwitch3').change(function () {
                if(this.checked) 
                    $("#labelCustomSwitch3").html('ON');
                else
                    $("#labelCustomSwitch3").html('OFF');
            });
            
            $('#submit').click(function (event) {       
                var prod_name = $("#prod_name").val();
                var short_name = $("#short_name").val();
                var valid = true;
                
                if(prod_name && short_name) {
                    if(prod_name.length>50) {
                        Swal.fire('Attention','Product Name Exceeds 50 chars','error');
                        valid = false;
                    }
                    else if(short_name.length>15) {
                        Swal.fire('Attention','Short Name should below 15 chars length','error');
                        valid = false;
                    }
                    else if(!isValidGstData()) {
                        valid = false;
                    }
                    else {
                        $.ajax({
                            url: "{{ route('products.unique') }}",
                            type: "GET",
                            async: false,
                            data: {
                                id: {{$product->id}},
                                prod_name: prod_name,
                                short_name: short_name
                            },
                            dataType: 'json',
                            success: function (data) {
                                if(data.name_count > 0) {
                                    event.preventDefault();
                                    Swal.fire('Attention','Product Name Already Exists','error');
                                    valid = false;
                                }
                                else if(data.short_name_count > 0) {
                                    event.preventDefault();
                                    Swal.fire('Attention','Short Name Already Defined','error');
                                    valid = false;
                                }
                            }
                        });
                    }
                }
                
                if(!valid) {
                    event.preventDefault();
                    return false;
                }
            });

            function isValidGstData() {
                var hsn_code = $("#hsn_code").val();
                var gst = parseFloat($("#gst").val());
                var sgst = parseFloat($("#sgst").val());
                var cgst = parseFloat($("#cgst").val());
                var igst = parseFloat($("#igst").val());

                let valid = false;
                let isnum = /^\d+$/.test(hsn_code);                
                let is_taxable = $("#rdo_taxable").is(":checked");
                if(!hsn_code) {
                    Swal.fire('Attention','Please Enter HSN Code','error');
                }
                else if(!isnum) {
                    Swal.fire('Attention','HSN Code must have only digits' + hsn_code,'error');
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

            $('#reset').click(function () {
                $('[data-repeater-list]').empty();
            });

        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script> 
    <!-- Repeater  -->
    <script src="{{ asset('plugins/repeater/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.form-repeater.js') }}"></script>
@stop