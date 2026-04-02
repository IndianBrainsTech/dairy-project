@extends('app-layouts.admin-master')

@section('title', $page_title)

@section('headerStyle')
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') {{ $page_title }} @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Permissions @endslot
                    @slot('item3') Users @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">

                    @if(session('success'))
                        <div class="alert alert-success">
                            @php
                                $isSuccess = true;
                                $msg = session('success');
                                echo $msg;
                            @endphp
                        </div>
                    @elseif(session('error'))
                        <div class="alert alert-danger">
                            @php
                                $isSuccess = false;
                                $msg = session('error');
                            @endphp
                            {{ $msg }}
                        </div>
                    @endif

                    <form class="mb-0" method="post" action="{{ $form_action }}" enctype="multipart/form-data">
                        @csrf
                        @if ($form_mode === \App\Enums\FormMode::EDIT)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card mb-0">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group row mt-2">
                                                    <label for="txt-name" class="col-sm-4 col-form-label">Name <small class="text-danger font-13">*</small></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" 
                                                            name="name" 
                                                            value="{{ old('name', $user->name ?? '') }}" 
                                                            class="form-control @error('name') is-invalid @enderror" 
                                                            required>
                                                        @error('name')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="ddl-role" class="col-sm-4 col-form-label">Role <small class="text-danger font-13">*</small></label>
                                                    <div class="col-sm-8">
                                                        <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                                            <option value="">Select</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}"
                                                                    @selected(old('role_id', $user->role_id ?? '') == $role->id)>
                                                                    {{ $role->display_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('role')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="txt-email" class="col-sm-4 col-form-label">Email &nbsp;</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" 
                                                            name="email" 
                                                            value="{{ old('email', $user->email ?? '') }}" 
                                                            class="form-control @error('email') is-invalid @enderror">
                                                        @error('email')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row mt-2">
                                                    <label for="txt-user-name" class="col-sm-4 col-form-label">User Name <small class="text-danger font-13">*</small></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" 
                                                            name="user_name" 
                                                            value="{{ old('user_name', $user->user_name ?? '') }}" 
                                                            class="form-control @error('user_name') is-invalid @enderror" 
                                                            required>
                                                        @error('user_name')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row mt-2">
                                                    <label for="txt-password" class="col-sm-4 col-form-label">Password <small class="text-danger font-13">*</small></label>
                                                    <div class="col-sm-8">
                                                        <input type="password" 
                                                            name="password"
                                                            class="form-control @error('password') is-invalid @enderror"
                                                            {!! $form_mode === \App\Enums\FormMode::CREATE 
                                                                ? 'required' 
                                                                : 'placeholder="Leave blank to keep password"' !!}>
                                                        @error('password')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <label for="photo" class="mx-auto mt-4">Photo</label>
                                                    <input type="file"
                                                        name="photo"
                                                        class="dropify @error('photo') is-invalid @enderror"
                                                        data-default-file="{{ isset($user) && $user->photo ? asset('mystorage/users/' . $user->photo) : '' }}"
                                                        accept="image/*">
                                                    @error('photo')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row justify-content-center">
                                            <button type="reset" class="btn btn-secondary mr-3">Clear</button>
                                            <button type="submit" class="btn btn-primary mx-2 px-3">Submit</button>
                                        </div>
                                    </div><!--end card-body-->
                                </div><!--end card-->

                            </div><!--end col-->
                        </div><!--end row-->
                    </form><!--end form-->
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

            doInit();

            function doInit() {
                $('a[href="#MenuMasters"]').click();
                $('.dropify').dropify();

                @if(session('success'))
                    Swal.fire('Success!', "{{ session('success') }}", 'success')
                        .then(() => window.location.replace("{{ route('permissions.users.index') }}"));
                @endif
            }
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/dropify/js/dropify.min.js') }}"></script>
@stop