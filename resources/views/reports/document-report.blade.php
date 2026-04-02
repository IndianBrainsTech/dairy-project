@extends('app-layouts.admin-master')

@section('title', 'Document Summary')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-action {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .my-action:hover {
            color: blue;
        }

        .w-100 {
            width: 100%;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Document Summary @endslot
                    @slot('item1') Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
 
                        <div>
                            <form method="post" action="{{ route('reports.document') }}">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="from_date" id="from-date" value="{{ $dates['from'] }}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="to_date" id="to-date" value="{{ $dates['to'] }}" class="my-control">
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <button id="btn-print" type="button" class="btn btn-pink py-1 mr-2" title="Print Report"><i class="fa fa-print"></i></button>
                            </form>
                        </div>
                        <hr/>

                        @if(!$records)
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found!
                            </div>
                        @else
                            <div id="report-div">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="print-header">
                                            <h3 class="text-center pb-2" style="color:maroon">Aasaii Food Productt</h3>
                                            <h3 class="text-center pb-2" style="color:blue">Document Summary</h3>
                                            <h4 class="text-center pb-3">{{ formatDateRange($dates['from'], $dates['to']) }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <table id="reportTable" class="text-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th rowspan="2">Document</th>
                                            <th colspan="2">Number</th>
                                            <th rowspan="2">Total</th>
                                            <th rowspan="2">Pending</th>
                                            <th rowspan="2">Cancelled</th>
                                            <th rowspan="2">Net Total</th>
                                        </tr>
                                        <tr>
                                            <th>From</th>
                                            <th>To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $fields = ['total', 'pending', 'cancelled', 'net_total'];
                                        @endphp
                                        @foreach($records as $record)
                                            <tr>
                                                <td class="text-left pl-3">{{ $record['document'] }} </td>
                                                <td>{{ $record['from'] }}</td>
                                                <td>{{ $record['to'] }}</td>
                                                @foreach($fields as $field)
                                                    @if($record[$field] != 0)
                                                        <td class="my-action" data-count="{{ $field }}">{{ $record[$field] }}</td>
                                                    @else
                                                        <td></td>
                                                    @endif
                                                @endforeach                                                
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
    </div><!--container-->

    <!-- Start of Records Modal -->
    <div class="modal fade" id="modal-records" tabindex="-1" role="dialog" aria-labelledby="modalRecordsLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal-title">Document (CountType)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-sm table-bordered nowrap text-nowrap" style="overflow-y:auto; width:100%">
                                    <thead class="thead-light">
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>   
                </div>
            </div>
        </div>
    </div>
    <!-- End of Records Modal -->
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

            $('#from-date').change(function() {
                var date = $(this).val();
                $('#to-date').attr('min',date);
            });

            $('.my-action').on('click', function () {                
                let documentType = $(this).closest('tr').find('td:first').text().trim();
                let countType = id = $(this).attr('data-count');
                $.ajax({
                    url: "{{ route('reports.document.detail') }}",
                    type: "GET",
                    data: {
                        document_type : documentType,
                        count_type    : countType,
                        from_date     : "{{ $dates['from'] }}",
                        to_date       : "{{ $dates['to'] }}",
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        constructTable(response);
                        let title = documentType + " (" + countType + ")";
                        $('#modal-title').html(title);
                        $('#modal-records').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $('#btn-print').on('click', function () {
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#from-date').trigger('change');            

            function constructTable(response) {
                const titles = response.titles;
                const records = response.records;
                const alignments = response.alignments;

                // Destroy existing DataTable if already initialized
                if ($.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable().destroy();
                }

                // Clear existing content
                $('#datatable thead').empty();
                $('#datatable tbody').empty();

                // Construct <thead> with inputs
                let theadRow = $("<tr>");
                theadRow.append(`<th class="text-center">S.No</th>`);
                titles.forEach((title, i) => {
                    theadRow.append(`
                        <th class="text-center no-sort">
                            <input type="text" class="my-control w-100" placeholder="${title}" data-index="${i}" />
                        </th>
                    `);
                });
                $('#datatable thead').append(theadRow);

                // Construct <tbody>
                records.forEach((row, index) => {
                    let tr = $('<tr>');
                    tr.append(`<td class="text-center">${index + 1}</td>`);
                    row.forEach((cell, i) => {
                        const align = alignments?.[i] || 'text-left';
                        tr.append(`<td class="${align}">${cell}</td>`);
                    });
                    $('#datatable tbody').append(tr);
                });

                setupTableFilters();
            }

            function setupTableFilters() {
                // Initialize DataTable
                const table = $('#datatable').DataTable({
                    dom: 't',
                    paging: false,                    
                });

                // Filter event handler
                $('#datatable thead').on('keyup', 'input', function () {
                    const columnIndex = $(this).data('index');
                    table
                        .column(columnIndex + 1) // Skip S.No
                        .search(this.value)
                        .draw();
                });

                // Prevent sorting on input click
                $('#datatable thead').on('click', 'input', function (e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>  
    <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
@stop