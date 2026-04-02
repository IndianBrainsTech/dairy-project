@extends('app-layouts.admin-master')

@section('title', 'Job Works')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Job Work List @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Job Work @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('job-work.index') }}">
                            @csrf
                            <label class="my-text">From</label>
                            <input type="date" name="from_date" id="from_date" value="{{ $dates['from'] }}" class="my-control">
                            <label class="my-text">To</label>
                            <input type="date" name="to_date" id="to_date" value="{{ $dates['to'] }}" class="my-control">
                            <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 ml-3"/>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap text-center" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr> 
                                        <th data-priority="6">S.No</th>
                                        <th data-priority="5">Date</th>
                                        <th data-priority="1">Number</th>
                                        <th data-priority="4" class="text-left pl-2">Customer</th>                                        
                                        <th data-priority="3">Status</th>
                                        <th data-priority="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($job_works as $job_work)                                        
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ displayDate($job_work->job_work_date) }}</td>
                                            <td>{{ $job_work->job_work_num }}</td>
                                            <td class="text-left pl-2">{{ $job_work->customer_name }}</td>                                            
                                            <td>{!! getJobWorkStatusWithBadge($job_work->invoice_status) !!}</td>
                                            <td><a href="#" class="show mr2" data-job-work="{{ $job_work->job_work_num }}"><i class="dripicons-preview text-primary font-20"></i></a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
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

            $('#from_date').change(function() {
                let date = $(this).val();
                $('#to_date').attr('min',date);
            });

            $("#from_date").trigger('change');

            function getJobWorkNumbers() {
                let table = $('#datatable').DataTable();
                let jobWorkNums = table.column(2,{search:'applied'}).data().toArray();
                return jobWorkNums;
            }

            $('body').on('click', '.show', function (event) {
                let jobWorkNum = $(this).attr('data-job-work');
                let jobWorks = getJobWorkNumbers();
                
                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('job-work.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add the data as hidden inputs
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'job_work_num',
                    'value': jobWorkNum
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'job_works',
                    'value': jobWorks
                }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop