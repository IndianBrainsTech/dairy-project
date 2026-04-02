@extends('app-layouts.admin-master')

@section('title', 'View Petrol Bunk')

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
                    @slot('title') View Petrol Bunk @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Transport @endslot
                    @slot('item3') Petrol Bunks @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-md-10 col-lg-6">
                <div class="card px-2">
                    <div class="card-body">
                        <h4 class="mt-1 mb-3">{{ $bunk->name }}</h4>
                        <div class="row my-2">
                            <div class="col-4">Code</div>
                            <div class="col-8" style="color:blue">{{ $bunk->code }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">Address</div>
                            <div class="col-8" style="color:blue">{{ $bunk->address }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">PIN Code</div>
                            <div class="col-8" style="color:blue">{{ $bunk->pin_code }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">Contact Number</div>
                            <div class="col-8" style="color:blue">{{ $bunk->contact_number }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">Email Address</div>
                            <div class="col-8" style="color:blue">{{ $bunk->email }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">PAN</div>
                            <div class="col-8" style="color:blue">{{ $bunk->pan }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">GST Number</div>
                            <div class="col-8" style="color:blue">{{ $bunk->gst_number }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">TDS Status</div>
                            <div class="col-8" style="color:blue">{{ $bunk->tds_status->label() }}</div>
                        </div>

                        <h6 class="mt-4" style="color:#fd3c97">Banking Info :</h6>
                        <div class="row my-2">
                            <div class="col-4">Bank</div>
                            <div class="col-8" style="color:blue">{{ $bunk->bank->name }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">Branch</div>
                            <div class="col-8" style="color:blue">{{ $bunk->branch->name }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">IFSC</div>
                            <div class="col-8" style="color:blue">{{ $bunk->branch->ifsc }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">Account Holder</div>
                            <div class="col-8" style="color:blue">{{ $bunk->account_holder }}</div>
                        </div>
                        <div class="row my-2">
                            <div class="col-4">Account Number</div>
                            <div class="col-8" style="color:blue">{{ $bunk->account_number }}</div>
                        </div>
                        <hr/>

                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Left side: Edit + Status buttons -->
                            <div>
                                @if($bunk->status === 'ACTIVE')
                                    <button type="button"
                                            class="btn btn-primary btn-sm px-3 mr-2"
                                            onclick="window.location='{{ route('bunks.edit', ['bunk' => $bunk->id]) }}'">
                                        Edit
                                    </button>
                                @endif

                                <form action="{{ route('bunks.status', ['bunk' => $bunk->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')

                                    @if($bunk->status === 'ACTIVE')
                                        <button type="submit" class="btn btn-warning btn-sm px-2" aria-label="Set petrol bunk inactive">
                                            Set Inactive
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-secondary btn-sm px-2" aria-label="Set petrol bunk active">
                                            Set Active
                                        </button>
                                    @endif
                                </form>
                            </div>

                            <!-- Right side: Delete button -->
                            <div>
                                <button type="button" id="btn-delete" class="btn btn-danger btn-sm px-3"
                                        aria-label="Delete petrol bunk" data-toggle="tooltip" data-placement="top"
                                        title="{{ $hasDieselBills ? 'This petrol bunk cannot be deleted because it is linked to other records (e.g., Diesel Bill).' : 'Delete petrol bunk' }}"
                                        {{ $hasDieselBills ? 'disabled' : '' }}>
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
                    .then(() => window.location.replace("{{ route('bunks.index') }}"));
            @endif

            @if(!$hasDieselBills)
                $('#btn-delete').on('click', function() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to delete the petrol bunk?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, close',
                    })
                    .then((result) => {
                        if (result.value) {
                            const id = "{{ $bunk->id }}";
                            $.ajax({
                                url: "{{ route('bunks.destroy', ['bunk' => '__ID__']) }}".replace('__ID__', id),
                                method: 'DELETE',
                                dataType: "json"
                            })
                            .done(response => {
                                console.log("AJAX Success:", response);
                                Swal.fire('Deleted!',response.message,'success')
                                    .then(() => window.location.replace("{{ route('bunks.index') }}"));
                            })
                            .fail((xhr, status, error) => {
                                handleAjaxError(xhr, status, error);
                            });
                        }
                    });
                });
            @endif
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>    
@stop