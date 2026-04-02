@extends('app-layouts.admin-master')

@section('title', 'Receipt Date Control Settings')

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
                @component('app-components.breadcrumb-2')
                    @slot('title') Receipt Date Control Settings @endslot
                    @slot('item1') Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered nowrap text-center mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Entry Type</th>
                                        <th>Days Before</th>
                                        <th>Days After</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr data-mode="cash">
                                        <td>
                                            Cash
                                        </td>
                                        <td>
                                            <input type="number" 
                                                class="app-control txt-days-before text-center" 
                                                value="{{ $settings['cash']->days_before ?? 0 }}" 
                                                min="0" max="364" disabled>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                class="app-control txt-days-after text-center" 
                                                value="{{ $settings['cash']->days_after ?? 0 }}" 
                                                min="0" max="364" disabled>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                class="btn btn-link btn-icon btn-edit p-0 mx-1" 
                                                data-mode="cash" 
                                                title="Edit">
                                                <i class="fas fa-edit text-info font-16"></i>
                                            </button>

                                            <button type="button" 
                                                class="btn btn-link btn-icon btn-update p-0 mx-1 d-none" 
                                                data-mode="cash" 
                                                title="Update">
                                                <i class="fas fa-save text-primary font-16"></i>
                                            </button>

                                            <button type="button" 
                                                class="btn btn-link btn-icon btn-reset p-0 mx-1 d-none" 
                                                data-mode="cash" 
                                                title="Reset">
                                                <i class="mdi mdi-close-box-outline text-warning font-18"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <tr data-mode="bank">
                                        <td>
                                            Bank
                                        </td>
                                        <td>
                                            <input type="number" 
                                                class="app-control txt-days-before text-center" 
                                                value="{{ $settings['bank']->days_before ?? 0 }}" 
                                                min="0" max="364" disabled>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                class="app-control txt-days-after text-center" 
                                                value="{{ $settings['bank']->days_after ?? 0 }}" 
                                                min="0" max="364" disabled>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                class="btn btn-link btn-icon btn-edit p-0 mx-1" 
                                                data-mode="bank" 
                                                title="Edit">
                                                <i class="fas fa-edit text-info font-16"></i>
                                            </button>

                                            <button type="button" 
                                                class="btn btn-link btn-icon btn-update p-0 mx-1 d-none" 
                                                data-mode="bank" 
                                                title="Save">
                                                <i class="fas fa-save text-primary font-16"></i>
                                            </button>

                                            <button type="button" 
                                                class="btn btn-link btn-icon btn-reset p-0 mx-1 d-none" 
                                                data-mode="bank" 
                                                title="Reset">
                                                <i class="mdi mdi-close-box-outline text-warning font-18"></i>
                                            </button>
                                        </td>
                                    </tr>
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

            $('body').on('click', '.btn-edit', function (event) {
                // Get mode
                const mode = $(this).data('mode');

                // Locate the corresponding row
                const $row = $('tr[data-mode="' + mode + '"]');

                // Store last saved values for reset
                $row.find('input[type="number"]').each(function () {
                    $(this).data('old-value', $(this).val());
                });

                // Enable input controls in this row
                $row.find('input[type="number"]').prop('disabled', false);

                // Toggle buttons
                $row.find('.btn-edit').addClass('d-none');
                $row.find('.btn-update, .btn-reset').removeClass('d-none');
            });

            $('body').on('click', '.btn-reset', function () {
                const mode = $(this).data('mode');
                const $row = $('tr[data-mode="' + mode + '"]');

                // Restore old values
                $row.find('input[type="number"]').each(function () {
                    const oldValue = $(this).data('old-value');
                    if (oldValue !== undefined) {
                        $(this).val(oldValue);
                    }
                });

                // Disable inputs again
                $row.find('input[type="number"]').prop('disabled', true);

                // Toggle buttons back
                $row.find('.btn-edit').removeClass('d-none');
                $row.find('.btn-update, .btn-reset').addClass('d-none');

            });

            $('body').on('click', '.btn-update', function () {
                const mode = $(this).data('mode');
                const $row = $('tr[data-mode="' + mode + '"]');

                const daysBefore = $row.find('.txt-days-before').val();
                const daysAfter  = $row.find('.txt-days-after').val();

                $(this).prop('disabled', true);
                $.ajax({
                    url: "{{ route('settings.date.update') }}",
                    method: "PUT",
                    data: {
                        'mode': mode,
                        'days_before': daysBefore,
                        'days_after': daysAfter,
                    },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        // Update stored "last saved" values
                        $row.find('.txt-days-before').data('old-value', daysBefore);
                        $row.find('.txt-days-after').data('old-value', daysAfter);

                        // Lock inputs again
                        $row.find('input[type="number"]').prop('disabled', true);

                        // Toggle buttons back to normal state
                        $row.find('.btn-edit').removeClass('d-none');
                        $row.find('.btn-update, .btn-reset').addClass('d-none');

                        // Success message
                        Swal.fire('Success!', response.message, 'success');
                    }
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $(this).prop('disabled', false);
                });
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop
