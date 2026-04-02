@extends('app-layouts.admin-master')

@section('title', 'Price Masters')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page Header: Title & Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Price Masters @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Deals & Pricing @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @include('app-partials.index-header-2', [
                            'countLabel'    => 'Master',
                            'count'         => $masters->count(),
                            'createUrl'     => route('price-masters.create'),
                            'adjustmentUrl' => route('price-masters.adjust.create'),
                        ])

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="max-width:40px">S.No</th>
                                        <th class="text-center">Document</th>
                                        <th class="text-center">Effect Date</th>
                                        <th class="text-left pl-2">Narration</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="max-width:60px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($masters as $master)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $master->document_number }}</td>
                                            <td class="text-center">{{ $master->effect_date_for_display }}</td>
                                            <td class="text-left pl-2">{{ $master->narration }}</td>
                                            <th class="text-center">{!! getStatusWithBadge($master->status->label()) !!}</th>                                            
                                            <td class="text-center">
                                                <a href="{{ route('price-masters.show', $master) }}" class="d-inline-flex align-items-center">
                                                    <i class="dripicons-preview text-primary font-20"></i>
                                                </a>
                                                &nbsp;
                                                <a href="{{ route('price-masters.clone.create', $master) }}">
                                                    <i class="fas fa-clone text-info font-16"></i>
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
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 25,
            } );

            $('.dropdown-menu .dropdown-item').on('click', function (e) {
                let newStatus = $(this).text();
                let oldStatus = $('#btn-status').text().trim();
                if(newStatus != oldStatus) {
                    window.location.href = "{{ route('price-masters.index') }}" + "?status=" + encodeURIComponent(newStatus);
                }
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop