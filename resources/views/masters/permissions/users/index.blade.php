@extends('app-layouts.admin-master')

@section('title', 'Users')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />    
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Users @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Permissions @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="cols-12 col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-pink btn-round font-weight-medium px-3">
                                {{ $users->count() }} {{ Str::plural('User', $users->count()) }}
                            </button>
                            <a href="{{ route('permissions.users.create') }}" class="btn btn-gradient-primary px-3">
                                <i class="mdi mdi-plus-circle-outline mr-2"></i>Add User
                            </a>
                        </div>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-left pl-2">Name</th>
                                        <th class="text-left pl-2">Role</th>
                                        <th class="text-left pl-2">User Name</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        @if($user->role_id !== \App\Enums\Roles::MASTER_ADMIN_ID)
                                            <tr>
                                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                                <td class="text-left pl-2">{{ $user->name }}</td>
                                                <td class="text-left pl-2">{{ $user->role->display_name }}</td>
                                                <td class="text-left pl-2">{{ $user->user_name }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('permissions.users.show', ['user' => $user->id]) }}">
                                                        <i class="dripicons-preview text-primary font-20"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
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

            $('#datatable').DataTable({
                paging: false,
                info: false,
                searching: true,
                dom: 'ft',
            });
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop