@extends('app-layouts.admin-master')

@section('title', 'TDS Master')

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
                    @slot('title') TDS Master @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Taxation @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">TDS Master</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_tds" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_tds"><i class="mdi mdi-plus-circle-outline mr-2"></i>New TDS Data</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered text-center">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Effect Date</th>
                                    <th>TDS Limit</th>                                    
                                    <th>With PAN</th>
                                    <th>Without PAN</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($tds_masters as $tds_data)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ displayDate($tds_data->effect_date) }}</td>
                                            <td>{{ formatNumberWithCommas($tds_data->tds_limit) }}</td>
                                            <td>{{ $tds_data->with_pan }} %</td>
                                            <td>{{ $tds_data->without_pan }} %</td>                                               
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
    <div class="modal fade" id="modal_tds" tabindex="-1" role="dialog" aria-labelledby="modalTaxLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_tds_title">Add TDS Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_tds">
                    <input type="hidden" id="tds_id" name="tds_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="effect_date" class="col-sm-5 col-form-label">Effect Date <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-5">
                                        <input type="date" class="form-control" id="effect_date" required="" name="effect_date">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="tds_limit" class="col-sm-5 col-form-label">TDS Limit <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-5">
                                        <input type="number" class="form-control" id="tds_limit" name="tds_limit" required="" maxlength="10">                                        
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="with_pan" class="col-sm-5 col-form-label">With PAN <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-5 input-group">
                                        <input type="number" class="form-control" id="with_pan" required="" name="with_pan" min="0" max="50">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="without_pan" class="col-sm-5 col-form-label">Without PAN <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-5 input-group">
                                        <input type="number" class="form-control" id="without_pan" required="" name="without_pan" min="0" max="50">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add TDS Data"/>
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

            var row = "{{ $row }}";
            if(row) 
                $("table tr:eq(" + row +")").addClass("table-primary");

            $('body').on('click', '#add_tds', function (event) {
                event.preventDefault();                
                $('#effect_date').val("");
                $('#tds_limit').val("");
                $('#with_pan').val("");
                $('#without_pan').val("");
                
                var edate = '<?php echo $effect_date; ?>';
                if(edate != '')
                    $('#effect_date').attr('min',edate);

                $('#modal_tds').modal('show');
            });

            function isValid(effect_date,tds_limit,with_pan,without_pan) {
                let valid = false;                
                if(!effect_date) {
                    Swal.fire('Attention','Please Choose Effect Date','error');
                }
                else if(!tds_limit) {
                    Swal.fire('Attention','Please Enter TDS Limit','error');
                }
                else if(!with_pan) {
                    Swal.fire('Attention','Please Enter With PAN (%)','error');
                }
                else if(!without_pan) {
                    Swal.fire('Attention','Please Enter Without PAN (%)','error');
                }
                else if(with_pan<0 || with_pan>50) {
                    Swal.fire('Error','Incorrect Value Entered for With PAN (%)','error');
                }
                else if(without_pan<0 || without_pan>50) {
                    Swal.fire('Error','Incorrect Value Entered for Without PAN (%)','error');
                }
                else {
                    valid = true;
                }
                return valid;
            }

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();   
                                
                var effect_date = $("#effect_date").val();
                var tds_limit = $("#tds_limit").val();
                var with_pan = $("#with_pan").val();
                var without_pan = $("#without_pan").val();
                
                tds_limit = parseFloat(tds_limit);
                with_pan = parseFloat(with_pan);
                without_pan = parseFloat(without_pan);

                if(isValid(effect_date,tds_limit,with_pan,without_pan)) {
                    $.ajax({
                        url: "{{ route('tdsmaster.store') }}",
                        type: "POST",
                        data: {
                            id: "0",
                            effect_date: effect_date,                            
                            tds_limit: tds_limit,
                            with_pan: with_pan,
                            without_pan: without_pan
                        },
                        dataType: 'json',
                        success: function (data) {
                            $('#form_tds').trigger("reset");
                            $('#modal_tds').modal('hide');                                                
                            Swal.fire({
                                    title:'Success!',
                                    text:"TDS Data has been generated!",
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
                            var errorText = data.responseText;
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
