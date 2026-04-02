@extends('app-layouts.admin-master')

@section('title', 'View Item Master')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Item Master @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Purchase @endslot
                    @slot('item3') Items @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-md-10 col-lg-6">
                <div class="card px-2">
                    <div class="card-body">

                        <h4 class="mt-1 mb-3">{{ $master->name }}</h4>

                        <div class="row my-2">
                            <div class="col-4">Code</div>
                            <div class="col-8" style="color:blue">{{ $master->code }}</div>
                        </div>

                        <div class="row my-2">
                            <div class="col-4">Group</div>
                            <div class="col-8" style="color:blue">{{ $master->group->name }}</div>
                        </div>

                        <div class="row my-2">
                            <div class="col-4">HSN Code</div>
                            <div class="col-8" style="color:blue">{{ $master->hsn_code }}</div>
                        </div>

                        <div class="row my-2">
                            <div class="col-4">Tax Type</div>
                            <div class="col-8" style="color:blue">{{ $master->tax_type->label() }}</div>
                        </div>

                        @if($master->tax_type === \App\Enums\TaxType::TAXABLE)
                            <div class="row my-2">
                                <div class="col-4">GST</div>
                                <div class="col-8" style="color:blue">{{ $master->gst }} %</div>
                            </div>

                            <div class="row my-2">
                                <div class="col-4">SGST</div>
                                <div class="col-8" style="color:blue">{{ $master->sgst }} %</div>
                            </div>

                            <div class="row my-2">
                                <div class="col-4">CGST</div>
                                <div class="col-8" style="color:blue">{{ $master->cgst }} %</div>
                            </div>

                            <div class="row my-2">
                                <div class="col-4">IGST</div>
                                <div class="col-8" style="color:blue">{{ $master->igst }} %</div>
                            </div>
                        @endif

                        <div class="row my-2">
                            <div class="col-4">Created by</div>
                            <div class="col-8"><span style="color:blue">{{ $master->creator->name }}</span> at {{ displayDateTimeIST($master->created_at) }}</div>
                        </div>

                        @if($master->updated_by)
                            <div class="row my-2">
                                <div class="col-4">Updated by</div>
                                <div class="col-8"><span style="color:blue">{{ $master->updater->name }}</span> at {{ displayDateTimeIST($master->updated_at) }}</div>
                            </div>
                        @endif                        

                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <!-- Left side: Edit + Status buttons -->
                            <div>
                                @if($master->status === \App\Enums\MasterStatus::ACTIVE)
                                    <button type="button"
                                            class="btn btn-primary btn-sm px-3 mr-2"
                                            onclick="window.location='{{ route('purchase.items.items.edit', ['master' => $master->id]) }}'">
                                        Edit
                                    </button>
                                @endif

                                <form action="{{ route('purchase.items.items.toggle', ['master' => $master->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')

                                    @if($master->status === \App\Enums\MasterStatus::ACTIVE)
                                        <button type="submit" class="btn btn-warning btn-sm px-2" aria-label="Set item master inactive">
                                            Set Inactive
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-secondary btn-sm px-2" aria-label="Set item master active">
                                            Set Active
                                        </button>
                                    @endif
                                </form>
                            </div>

                            <!-- Right side: Delete button -->
                            <div>
                                <button type="button" id="btn-delete" class="btn btn-danger btn-sm px-3"
                                        aria-label="Delete item master" data-toggle="tooltip" data-placement="top">
                                        {{-- title="{{ $hasPurchaseOrders ? 'This item master cannot be deleted because it is linked to other records (e.g., Purchase Order).' : 'Delete item master' }}"
                                        {{ $hasPurchaseOrders ? 'disabled' : '' }}> --}}
                                    Delete
                                </button>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper-v1.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('a[href="#MenuMasters"]').click();

            @if(session('success'))
                Swal.fire('Success!', "{{ session('success') }}", 'success')
                    .then(() => window.location.replace("{{ route('purchase.items.items.index') }}"));
            @endif

            
                $('#btn-delete').on('click', function() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to delete the item master?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, close',
                    })
                    .then((result) => {
                        if (result.value) {
                            const id = "{{ $master->id }}";
                            $.ajax({
                                url: "{{ route('purchase.items.items.destroy', ['master' => '__ID__']) }}".replace('__ID__', id),
                                method: 'DELETE',
                                dataType: "json"
                            })
                            .done(response => {
                                console.log("AJAX Success:", response);
                                Swal.fire('Deleted!',response.message,'success')
                                    .then(() => window.location.replace("{{ route('purchase.items.items.index') }}"));
                            })
                            .fail((xhr, status, error) => {
                                handleAjaxError(xhr, status, error);
                            });
                        }
                    });
                });
            
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>    
@stop