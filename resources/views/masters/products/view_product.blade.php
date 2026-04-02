@extends('app-layouts.admin-master')

@section('title', 'Product')

@section('headerStyle')
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Product @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Products @endslot
                    @slot('item3') Products @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-6">
                                <img src="{{ asset('mystorage/products/' . $product->image) }}" alt="" class=" mx-auto  d-block" height="400">                                           
                                <p style="text-align:center;margin-top:4px">Image &nbsp;&nbsp;&nbsp;<a href="" id="photo" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_image_upload" data-id="photo"><i class="fas fa-edit text-info font-16"></i></a></p>
                            </div><!--end col-->

                            <div class="col-lg-6 align-self-center">
                                <div class="single-pro-detail">
                                    <h3 class="pro-title">{{ $product->name }}</h3>
                                    <p class="mb-1">{{ $product->short_name }}</p>
                                    <p class="text-muted mb-0">{{ $product->description }}</p> 
                                    <h2 class="pro-price"><small>&#8360;</small>. {{ $product->mrp }}</h2>

                                    <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Specifications :</h6>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">FAT</div>
                                        <div class="col-md-8" style="color:blue">{{ $product->fat }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">SNF</div>
                                        <div class="col-md-8" style="color:blue">{{ $product->snf }}</div>
                                    </div>    
                                    
                                    <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Taxations :</h6>                                    
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Product Group</div>
                                        <div class="col-md-8" style="color:blue">{{ $product->prod_group->name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">HSN Code</div>
                                        <div class="col-md-8" style="color:blue">{{ $product->hsn_code }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Tax Type</div>
                                        <div class="col-md-8" style="color:blue">{{ $product->tax_type }}</div>
                                    </div>
                                    
                                    @if($product->tax_type == "Taxable")
                                        <div class="row" style="margin-bottom:10px; margin-left:16px;">
                                            <div class="col-md-8">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm text-center mb-0">
                                                        <thead>
                                                        <tr>
                                                            <th>GST</th>
                                                            <th>SGST</th>
                                                            <th>CGST</th>
                                                            <th>IGST</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <th>{{ $product->gst }}%</th>
                                                            <td>{{ $product->sgst }}%</td>
                                                            <td>{{ $product->cgst }}%</td>
                                                            <td>{{ $product->igst }}%</td>
                                                        </tr>
                                                        </tbody>
                                                    </table><!--end /table-->
                                                </div><!--end /tableresponsive-->
                                            </div>                                        
                                        </div> 
                                    @endif
                                </div>
                            </div><!--end col-->                                            
                        </div><!--end row-->

                        <div class="row">
                            <div class="col-lg-6">                                                                    
                                <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Units & Measurements :</h6>
                                @foreach($units as $unit)
                                    @if($unit->prim_unit == 1)
                                        <div class="row" style="margin-bottom:10px; margin-left:16px">
                                            <div class="col-md-4">Primary Unit</div>
                                            <div class="col-md-8" style="color:blue">{{ $unit->unit_name }}</div>
                                        </div>
                                        <div class="row" style="margin-bottom:10px; margin-left:16px">
                                            <div class="col-md-4">Primary Unit Price</div>
                                            <div class="col-md-8" style="color:blue">Rs.{{ $unit->price }}</div>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Additional Units</div>                                        
                                </div>
                                @foreach($units as $unit)
                                    @if($unit->prim_unit == 0)
                                        <div class="row" style="margin-bottom:10px; margin-left:32px; color:blue">
                                            <div class="col-md-3" data-toggle="tooltip" data-placement="left" title="Unit">{{ $unit->unit_name }}</div>
                                            <div class="col-md-3" data-toggle="tooltip" data-placement="left" title="Unit Price">Rs.{{ $unit->price }}</div>
                                            <div class="col-md-4" data-toggle="tooltip" data-placement="left" title="Conversion to Primary">{{ $unit->conversion }} {{ $product->unit_name }}</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="col-lg-6">
                                <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Visibility Control :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Mobile App</div>
                                    <div class="col-md-8" style="color:blue">
                                        @if($product->visible_app)
                                            <div class="custom-switch switch-pink">
                                                <input type="checkbox" class="custom-control-input" checked>
                                                <label class="custom-control-label">ON</label>
                                            </div>
                                        @else
                                            <div class="custom-switch">
                                                <input type="checkbox" class="custom-control-input" unchecked>
                                                <label class="custom-control-label">OFF</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Regular Invoice</div>
                                    <div class="col-md-8" style="color:blue">
                                        @if($product->visible_invoice)
                                            <div class="custom-switch switch-pink">
                                                <input type="checkbox" class="custom-control-input" checked>
                                                <label class="custom-control-label">ON</label>
                                            </div>
                                        @else
                                            <div class="custom-switch">
                                                <input type="checkbox" class="custom-control-input" unchecked>
                                                <label class="custom-control-label">OFF</label>
                                            </div>
                                        @endif
                                    </div>
                                </div> 
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Bulk Milk Invoice</div>
                                    <div class="col-md-8" style="color:blue">
                                        @if($product->visible_bulkmilk)
                                            <div class="custom-switch switch-pink">
                                                <input type="checkbox" class="custom-control-input" checked>
                                                <label class="custom-control-label">ON</label>
                                            </div>
                                        @else
                                            <div class="custom-switch">
                                                <input type="checkbox" class="custom-control-input" unchecked>
                                                <label class="custom-control-label">OFF</label>
                                            </div>
                                        @endif
                                    </div>
                                </div> 
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Status</div>
                                    <div class="col-md-8" style="color:blue">
                                        @if($product->status == 'Active')
                                            <div class="custom-switch switch-pink">
                                                <input type="checkbox" class="custom-control-input" checked>
                                                <label class="custom-control-label">Active</label>
                                            </div>
                                        @else
                                            <div class="custom-switch">
                                                <input type="checkbox" class="custom-control-input" unchecked>
                                                <label class="custom-control-label">Inactive</label>
                                            </div>
                                        @endif
                                    </div>
                                </div> 
                            </div>
                        </div>
                        
                        <hr/>

                        <a href="{{ route('products.edit',['id'=>$product->id]) }}"><button class="btn btn-gradient-primary" type="button" style="width:90px; margin-right:20px">Edit</button></a>
                        @if($product->status == "Active")
                            <a href="{{ route('products.status',['id'=>$product->id]) }}"><button class="btn btn-gradient-danger" type="button" style="width:120px">Set Inactive</button></a>
                        @else
                            <a href="{{ route('products.status',['id'=>$product->id]) }}"><button class="btn btn-gradient-primary" type="button" style="width:110px">Set Active</button></a>
                        @endif
                        
                        @if(Session::has('success'))
                            <div class="alert alert-success" style="width:60%;align:center;margin-top:20px">
                                {{ Session::get('success') }}
                            </div>                        
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

    </div><!-- container -->

    <!-- Start of Image Upload Modal -->
    <div class="modal fade" id="modal_image_upload" tabindex="-1" role="dialog" aria-labelledby="modalImageUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Image Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_image_upload" method="post" action="{{ route('photos.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="id" name="id" value="{{ $product->id }}">
                    <input type="hidden" id="user" name="user" value="product">
                    <input type="hidden" id="tag" name="tag" value="NIL">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <input type="file" name="image_file" id="image_file" accept="image/*" class="dropify" />
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-primary" id="submit" value="Upload"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Image Upload Modal -->
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
            
            $('body').on('click', '#submit', function (event) {
                var image_name = $("#image_file").val();
                if(image_name) {
                    if(!isExtensionValid(image_name)) {
                        Swal.fire('Attention','Uploaded file is not an image','error');
                        event.preventDefault();
                    }                    
                }
                else {
                    Swal.fire('Attention','Please Select Image to Update','error');
                    event.preventDefault();
                }                
            });

        });  
    </script> 
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script> 
    <!-- Dropify  -->
    <script src="{{ asset('plugins/dropify/js/dropify.min.js') }}"></script>
    <script>
        $('.dropify').dropify();
    </script> 
@stop