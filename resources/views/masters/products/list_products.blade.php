@extends('app-layouts.admin-master')

@section('title', 'Products')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Nestable css -->
<link href="{{ asset('plugins/nestable/jquery.nestable.min.css') }}" rel="stylesheet" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Products @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Products @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">                                                    
                        <div style="width:100%">
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Products</h4></div>
                            <div style="width:40%;float:left">
                                <a href="{{ route('products.create') }}" id="add_product" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Product</a>
                                <button type="button" id="reorder" class="btn btn-gradient-pink px-4 float-right mt-0 mb-3 mr-3" data-toggle="modal" data-animation="bounce" data-target="#modal_reorder">Reordering</button>
                            </div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th>Product</th>
                                    <th>Group</th>                                    
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>
                                                <div class="media">
                                                    <img src="{{ asset('mystorage/products/' . $product->image) }}" alt="" class="thumb-sm mr-2">
                                                    <div class="media-body align-self-center text-truncate">
                                                        <!-- <h6 class="mt-0 mb-1 text-dark">{{ $product->name }}</h6> -->
                                                        <a href="#" class="mt-0 mb-1 text-dark" data-toggle="popover" data-trigger="focus" title="" data-content="{{ $product->description }}">
                                                            {{ $product->name }}
                                                        </a>
                                                        <p class="text-muted mb-0">{{ $product->short_name }}</p>
                                                    </div><!--end media-body-->
                                                </div>
                                            </td>
                                            <td>
                                                {{ $product->prod_group->name }}
                                            </td>
                                            <td>
                                                @if($product->status == "Active")
                                                    <span class="badge badge-md badge-boxed badge-soft-success">{{ $product->status }}</span>
                                                @else
                                                    <span class="badge badge-md badge-boxed badge-soft-danger">{{ $product->status }}</span>    
                                                @endif                                                
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('products.show',['id'=>$product->id]) }}" class="mr-2"><i class="dripicons-preview text-primary font-20"></i></a>
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
    
    <!-- Start of Product Reordering Modal -->
    <div class="modal fade" id="modal_reorder" tabindex="-1" role="dialog" aria-labelledby="modalProductReorderLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_reorder_title">Product Reordering</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_group">
                    <div class="modal-body" style="height:400px; overflow-y:auto; padding:0px">
                        <div class="card mb-0">
                            <div class="card-body"> 
                                <div class="custom-dd dd" id="nestable_list_1">
                                    <ol class="dd-list">
                                        @foreach($products as $product)
                                            <li class="dd-item dd3-item" data-id="{{ $product->id }}"> 
                                                <div class="dd-handle dd3-handle"></div>
                                                <div class="dd3-content dd3-content-p">{{ $product->name }}</div>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div><!--nastable-list-3--> 
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Cancel" />
                        <input type="submit" class="btn btn-primary px-3" id="submit" value="Save" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Product Reorder Modal -->
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

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "pageLength": -1,
            } );

            function getDataIds() {
                // Initialize an empty array to store data ids
                var dataIds = [];

                // Iterate through each list item
                $('.custom-dd .dd-list .dd-item').each(function() {
                    // Get the data-id attribute value and push it into the array
                    dataIds.push($(this).attr('data-id'));
                });

                // Convert the array to JSON if needed
                var jsonData = JSON.stringify(dataIds);

                console.log(dataIds); // Output the array of data ids
                console.log(jsonData); // Output the JSON string

                return jsonData;
            }

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();
                var dataIds = getDataIds();

                $.ajax({
                    url: "{{ route('products.reorder') }}",
                    type: "POST",
                    data: {
                        ids: dataIds
                    },
                    dataType: 'json',
                    success: function (data) {              
                        $('#form_group').trigger("reset");
                        $('#modal_reorder').modal('hide');                        
                        Swal.fire({
                                title:'Success!',
                                text:'Products Arranged Successfully',
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
                                text:data.responseText,
                                type:'warning',
                                confirmButtonColor: '#FF0000'
                            }
                        );
                    }
                });
            });            
        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>  
    <!-- Required datatable js -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>    
    <!-- Responsive examples -->
    <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    <!--Nestable-->
    <script src="{{ asset('plugins/nestable/jquery.nestable.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.nastable.init.js') }}"></script>
@stop