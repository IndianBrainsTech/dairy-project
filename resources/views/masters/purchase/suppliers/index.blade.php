@extends('app-layouts.admin-master')

@section('title', 'Suppliers')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Suppliers @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Purchase @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-10">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                            <!-- Count button -->
                            <button type="button" class="btn btn-pink btn-round font-weight-medium px-3 mb-2 mb-md-0">
                                {{ $masters->count() }} {{ Str::plural('Supplier', $masters->count()) }}
                            </button>

                            <!-- Right side buttons -->
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                
                                <!-- Active dropdown -->
                                <div class="btn-group dropright mr-3">
                                    <button id="btn-status" type="button" class="btn btn-outline-purple waves-effect waves-light" style="min-width:80px">
                                        {{ $status==='Active' ? 'Active' : 'All' }}
                                    </button>
                                    <button type="button" class="btn btn-info waves-effect waves-light dropdown-toggle-split dropdown-toggle" 
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="sr-only">Toggle Dropright</span>
                                        <i class="mdi mdi-chevron-right"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Active</a>
                                        <a class="dropdown-item" href="#">All</a>
                                    </div>
                                </div>

                                <!-- Create button -->
                                <a href="{{ route('suppliers.create') }}" class="btn btn-primary px-3 mb-2 mb-sm-0">
                                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Create
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="max-width:40px">S.No</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-left pl-2">Name</th>
                                        <th class="text-left pl-2">City</th>
                                        <th class="text-center">Contact Number</th>
                                        @if($status != "Active")
                                            <th class="text-center">Status</th>
                                        @endif
                                        <th class="text-center" style="max-width:60px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($masters as $master)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $master->code }}</td>
                                            <td class="text-left pl-2">{{ $master->name }}</td>
                                            <td class="text-left pl-2">{{ $master->city }}</td>
                                            <td class="text-center">{{ $master->contact_number }}</td>
                                            @if($status != "Active")
                                                <th class="text-center">{!! getStatusWithBadge($master->status->label()) !!}</th>
                                            @endif
                                            <td class="text-center">
                                                <a href="{{ route('suppliers.show', ['supplier' => $master->id]) }}">
                                                    <i class="dripicons-preview text-primary font-20"></i>
                                                </a>
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

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 10,
            } );

            $('.dropdown-menu .dropdown-item').on('click', function (e) {
                let newStatus = $(this).text();
                let oldStatus = $('#btn-status').text().trim();
                if(newStatus != oldStatus) {
                    window.location.href = "{{ route('suppliers.index') }}" + "?status=" + encodeURIComponent(newStatus);
                }
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop