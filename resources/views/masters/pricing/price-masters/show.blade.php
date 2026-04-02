@extends('app-layouts.admin-master')

@section('title', 'View Price Master')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page Header: Title & Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Price Master @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Deals & Pricing @endslot
                    @slot('item3') Price Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-6">
                                <h6 class="font-14 mb-3" style="color:#fd3c97">Document Header</h6>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-5">Document Number</div>
                                    <div class="col-md-7" style="color:blue">{{ $master->document_number }}</div>
                                </div>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-5">Document Date</div>
                                    <div class="col-md-7" style="color:blue">{{ $master->document_date_for_display }}</div>
                                </div>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-5">Effect Date</div>
                                    <div class="col-md-7" style="color:blue">{{ $master->effect_date_for_display }}</div>
                                </div>
                                <div class="row ml-2 mb-2">
                                    <div class="col-md-5">Narration</div>
                                    <div class="col-md-7" style="color:blue">{{ $master->narration }}</div>
                                </div>

                                <h6 class="font-14 mt-4 mb-3" style="color:#fd3c97">Applicable Customers</h6>
                                <div class="table-responsive table-container">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" width="60px">S.No</th>
                                                <th class="pl-2">Customer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($master->associated_customers as $customer)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="pl-2">{{ $customer->customer_name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <h6 class="font-14 mb-3" style="color:#fd3c97">Price List</h6>
                                <div class="table-responsive table-container">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" width="60px">S.No</th>
                                                <th>Product</th>
                                                <th class="text-center">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($master->associated_products as $product)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>{{ $product['name'] }}</td>
                                                    <td class="text-center">{{ $product['price'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr/>

                        <div>
                            @if($master->status === \App\Enums\PriceMasterStatus::ACTIVE)
                                <button type="button"
                                        class="btn btn-primary btn-sm px-3 mr-3"
                                        onclick="window.location='{{ route('price-masters.edit', $master) }}'">
                                    Edit
                                </button>
                            @endif

                            <form action="{{ route('price-masters.status.toggle', $master) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')

                                @if($master->status === \App\Enums\PriceMasterStatus::ACTIVE || 
                                    $master->status === \App\Enums\PriceMasterStatus::INACTIVE)
                                    @if($master->status === \App\Enums\PriceMasterStatus::ACTIVE)
                                        <button type="submit" class="btn btn-warning btn-sm px-2" aria-label="Set price master inactive">
                                            Set Inactive
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-secondary btn-sm px-2" aria-label="Set price master active">
                                            Set Active
                                        </button>
                                    @endif
                                @endif
                            </form>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
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

            setMenuItemActive('Masters','ul-deals-pricing','li-price-master');

            console.log(@json($master->associated_products));

            function setMenuItemActive(menuId, submenuId, menuItemId) {
                // Open main menu (simulate click)
                $('a[href="#Menu' + menuId + '"]').trigger('click');

                // Open submenu
                $('#' + submenuId)
                    .addClass('show')
                    .attr('aria-expanded', 'true')
                    .slideDown(0);

                // Set active class on menu item <li> and <a>
                $('#' + menuItemId).addClass('active');
                $('#' + menuItemId + ' > a').addClass('active');
            }

            @if(session('success'))
                Swal.fire('Success!', "{{ session('success') }}", 'success')
                    .then(() => window.location.replace("{{ route('price-masters.index') }}"));
            @endif
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop