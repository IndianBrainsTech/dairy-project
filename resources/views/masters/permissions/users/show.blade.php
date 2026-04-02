@extends('app-layouts.admin-master')

@section('title', 'User')

@section('headerStyle')
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Show User @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Permissions @endslot
                    @slot('item3') Users @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-4 text-center">
                                <img src="{{ asset('mystorage/users/' . $user->photo) }}" alt="Profile photo of {{ $user->name }}" role="img" class="mx-auto d-block" height="160px">
                                <p class="mt-2 mb-0">Photo 
                                    <a href="javascript:void(0)" id="photo" class="ml-2 text-info" data-toggle="modal" data-animation="bounce" data-target="#modal-upload" data-id="photo" title="Edit Photo" aria-label="Edit photo" role="button">
                                        <i class="fas fa-edit text-info font-16" aria-hidden="true"></i>
                                    </a>
                                </p>
                            </div>

                            <div class="col-lg-8">
                                <div class="row mt-4 mb-2">
                                    <div class="col-md-3">Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $user->name }}</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-3">Role</div>
                                    <div class="col-md-8" style="color:blue">{{ $user->role->display_name }}</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-3">Email</div>
                                    <div class="col-md-8" style="color:blue">{{ $user->email }}</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-3">User Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $user->user_name }}</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-3">Status</div>
                                    <div class="col-md-8" style="color:blue">{{ $user->status }}</div>
                                </div>
                            </div>
                        </div>
                        <hr/>

                        <a href="{{ route('permissions.users.edit', ['user'=>$user->id]) }}" class="btn btn-primary btn-sm px-3 mr-3">Edit</a>
                       
                        <form action="{{ route('permissions.users.status', ['user' => $user->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH') <!-- Optional: use PATCH if updating status only -->

                            @if($user->status === 'Active')
                                <button type="submit" class="btn btn-gradient-danger btn-sm px-2" aria-label="Set user inactive">
                                    Set Inactive
                                </button>
                            @else
                                <button type="submit" class="btn btn-secondary btn-sm px-2" aria-label="Set user active">
                                    Set Active
                                </button>
                            @endif
                        </form>
                        
                        @if(session('success'))
                            <div id="success-alert" class="alert alert-success mx-auto text-center mt-3 py-2">
                                {{ session('success') }}
                            </div>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container -->

    <!-- Start of Image Upload Modal -->
    <div class="modal fade" id="modal-upload" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modalImageUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImageUploadLabel">Photo Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="{{ route('photos.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <input type="hidden" name="user" value="user">
                    <div class="modal-body">
                        <div class="row mx-1">
                            <div class="col-md-12">
                                <div class="form-group row mb-1">
                                    <input type="file" name="image_file" id="file-photo" accept="image/*" class="dropify" aria-label="Upload photo" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mx-auto" id="btn-submit" aria-label="Upload photo">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Image Upload Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/file-helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function doInit() {
                $('a[href="#MenuTransactions"]').click();
                $('.dropify').dropify();

                setTimeout(function () {
                    $('#success-alert').fadeOut('slow');
                }, 1500); // 1.5 seconds

                $('body').on('click', '#btn-submit', function (event) {
                    const fileName = $("#file-photo").val();
                    validateImageFileBeforeSubmit(fileName, event);
                });
            }

            doInit();
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/dropify/js/dropify.min.js') }}"></script>
@stop