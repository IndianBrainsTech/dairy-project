@extends('app-layouts.admin-master')

@section('title', 'Receipts')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .my-link {
            color: orange;
            text-decoration-line: underline;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Make Receipts @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Receipts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
  
        <div class="row"> 
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="{{ route('receipts.make.index') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <label class="my-text mt-2">Date</label>
                                        <input type="date" name="date" id="date" value="{{$date}}" class="my-control ml-2" style="border: 1px solid #d3d3d3;border-radius:2px;padding-left:8px;padding-right:8px;">                                                                               
                                        <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>
                                    </div>
                                </div>
                            </div>
                        </form><!--end form-->
                        <hr/>
                        @if(!$table)
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found.
                            </div>
                        @else
                            <div class="table-responsive dash-social">
                                <table id="datatable" class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-left pl-2">Route</th>
                                            <th class="text-center">No of Receipts</th>
                                            <th class="text-right pr-2">Cash</th>
                                            <th class="text-right pr-2">Bank</th>
                                            <th class="text-right pr-2">Incentive</th>
                                            <th class="text-right pr-2">Deposit</th>
                                            <th class="text-right pr-2">Total</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($table as $row)
                                            <tr>
                                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                                <td class="text-left pl-2">{{ $row['route'] }}</td>
                                                <td class="text-center">{{ $row['count'] }}</td>
                                                <td class="text-right pr-2">{{ getEmptyForZero($row['cash']) }}</td>
                                                <td class="text-right pr-2">{{ getEmptyForZero($row['bank']) }}</td>
                                                <td class="text-right pr-2">{{ getEmptyForZero($row['incentive']) }}</td>
                                                <td class="text-right pr-2">{{ getEmptyForZero($row['deposit']) }}</td>
                                                <td class="text-right pr-2">{{ $row['total'] }}</td>
                                                <td class="text-center">
                                                    @if($row['status'] == "Pending")
                                                        <a href="" class="my-link" data-id="{{ $row['route_id'] }}">Pending</a>
                                                    @else
                                                        {{ $row['status'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        $(document).ready(function() {
            // Set up AJAX to include CSRF token in the header
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            function doInit() { 
                // restrictToTodayAndTomorrow('#invDate');
                restrictDate('#date');
            }
            function restrictDate(dateControl) {
                // Get today's date (local time)
                let today = new Date();
                let day1 = new Date('2025-02-01');

                // Add one day to today's date to allow tomorrow
                let tomorrow = new Date();
                tomorrow.setDate(today.getDate() + 1);

                // Format date as 'YYYY-MM-DD' (ensuring two-digit month and day)
                function formatDate(date) {
                    let yyyy = date.getFullYear();
                    let mm = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                    let dd = String(date.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                }

                // Get formatted dates
                let tomorrowFormatted = formatDate(tomorrow);
                let day1Formatted = formatDate(day1);

                // Set the min and max attributes on the date input
                $(dateControl).attr('min', day1Formatted);
                $(dateControl).attr('max', tomorrowFormatted);
            }

            $('body').on('click', '.my-link', function(event) {
                event.preventDefault();
                let id = $(this).data('id');
                let date = $('#date').val(); 
                let url = "{{ route('receipts.make.show') }}" + "?id=" + id + "&date=" + date;
                window.location.href = url;
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop