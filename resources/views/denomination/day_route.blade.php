@extends('app-layouts.admin-master')

@section('title', 'Day Route')

@section('headerStyle')
    <link href="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/css/my-style.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('assets/css/my-actxt.css')}}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Day Route (Denomination) @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Denomination @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{route('day.route.denomination')}}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <input type="date" name="date" id="date" value="{{$date}}" class="my-control ml-2">                                                                     
                                        <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>
                                        <button id="dayDenomination" class="btn btn-dark ml-3"><a href="#" class="text-white">Day Denomination</a></button>
                                    </div>
                                </div>
                            </div>
                        </form><!--end form-->
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th data-priority="5" class="text-center">S.No</th>
                                        <th data-priority="2">Route</th>                                        
                                        <th data-priority="1" class="text-center">No of Receipts</th>
                                        <th data-priority="3" class="text-center">Amount</th>                                        
                                        <th data-priority="4" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($routeDeno as $deno)
                                        <tr>                                            
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $deno['route'] }}</td>
                                            <td class="text-center">{{ $deno['no_of_receipt'] }}</td>
                                            <td class="text-center">{{ $deno['amount'] }}</td>
                                            <td class="text-center">
                                                <a href="#" class="view-route-deno" data-order="{{$deno['route_id']}}">
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
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ URL::asset('assets/js/input-restriction.js')}}"></script>
    <script src="{{ URL::asset('assets/js/helper.js')}}"></script>
    <script> 
        $(document).ready(function () {
            function getCsrfToken() {
                return $('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': $('meta[name="csrf-token"]').attr('content')
                });
            }

            function createAndSubmitForm(actionUrl, data) {
                let form = $('<form>', {
                    'method': 'POST',
                    'action': actionUrl
                });
                form.append(getCsrfToken());
                $.each(data, function (key, value) {
                    form.append($('<input>', {'type': 'hidden', 'name': key, 'value': value}));
                });
                form.appendTo('body').submit();
            }

            $('#datatable').dataTable({
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 50,
                "emptyTable": "No data available"
            });

            $('.view-route-deno').on('click', function () {
                let routeId = $(this).data('order');
                createAndSubmitForm('{{ route("route.denomination.view") }}', {
                    'routeId': routeId,
                    'date': '{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}'
                });
            });

            $('#dayDenomination').on('click', function () {
                createAndSubmitForm('{{ route("day.denomination.view") }}', {
                    'date': '{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}'
                });
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ URL::asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
@stop