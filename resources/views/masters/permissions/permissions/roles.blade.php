@extends('app-layouts.admin-master')

@section('title', 'Role Permissions')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .nav-tabs .nav-item .nav-link.active {
            color: #ffffff;
            background-color: #fd3c97;
        }

        tr.sub-head-row {
            background-color: #f1f1f1;
            font-weight: 600;
        }

        td.sub-head-col {
            background-color: #f1f1f1;
        }

        td.fixed-col-width {
            width: 150px;
        }

        label {
            padding-left: 4px !important;
            margin-right: 6px;
            margin-bottom: 0px;
        }

        .table td {
            white-space: nowrap; /* Prevent table content wrapping */
        }

        .checkbox label {
            white-space: nowrap;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Role Permissions @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Permissions @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row px-3">
                            <label for="ddl-role" class="col-form-label mr-3">Role <small class="text-danger font-13">*</small></label>
                            <select id="ddl-role" class="app-control mr-2" style="min-width: 200px; padding: 6px 10px" required>
                                <option value="">Select</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="btn-load" class="btn btn-primary btn-sm mx-3 px-3">Load</button>
                        </div>

                        @include('masters.permissions.permissions.layout')

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

            let roleId;
            let permissions;
            doInit();

            function doInit() {
                $("#div-message").text("No Role Selected!");
                $('#div-granted').hide();

                $('#btn-select-all').on('click', function() {
                    $('input[name="permissions[]"]').prop('checked', true);
                });

                $('#btn-clear-all').on('click', function() {
                    $('input[name="permissions[]"]').prop('checked', false);
                });

                $('#btn-load').on('click', getAndLoadPermissions);
                $('#btn-reset').on('click', resetPage);
                $('#btn-save').on('click', validateAndSavePermissions);
            }

            function getAndLoadPermissions() {
                roleId = $('#ddl-role').val();
                if(!roleId) {
                    Swal.fire('Sorry!', "Please select role" ,'warning');
                }
                else {
                    $('input[name="permissions[]"]').prop('checked', false);

                    $.ajax({
                        url: "{{ route('permissions.role-permissions.show', ['role' => '__ID__']) }}".replace('__ID__', roleId),
                        method: "GET",
                        dataType: "json"
                    })
                    .done(response => {
                        console.log("AJAX Success:", response);
                        permissions = response.data;
                        showGrantedPermissions();
                    })
                    .fail((xhr, status, error) => {
                        handleAjaxError(xhr, status, error);
                    });
                }
            }

            function resetPage() {
                $('#ddl-role').val(roleId);
                loadPermissions();
            }

            function validateAndSavePermissions() {
                roleId = $('#ddl-role').val();
                if(!roleId) {
                    Swal.fire('Sorry!', "Please select role" ,'warning');
                }
                else {
                    // Get all checked permission values
                    const selectedPermissions = $("input[name='permissions[]']:checked")
                        .map(function() {
                            return $(this).val();
                        }).get();

                    console.log(selectedPermissions);

                    if (!selectedPermissions || selectedPermissions.length === 0) {
                        const role = $('#ddl-role option:selected').text();
                        Swal.fire({
                            title: 'Confirm?',
                            html: `No Permissions Selected!<br/>Do you want to revoke all permissions for ${role}?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, do it!',
                            cancelButtonText: 'No, close',
                        })
                        .then((result) => {
                            if (result.value)
                                savePermissions(roleId, selectedPermissions);
                        });
                    }
                    else {
                        savePermissions(roleId, selectedPermissions);
                    }
                }
            }

            function showGrantedPermissions() {
                const role = $('#ddl-role option:selected').text();

                // If empty → show warning, hide list, stop.
                if (!permissions?.length) {
                    $("#div-message")
                        .text(`${role} does not have any permissions yet.`)
                        .removeClass("alert-outline-pink")
                        .addClass("alert-outline-warning");
                    $('#div-granted').hide();
                    return;
                }

                // If not empty → pick the correct message, show list, load permissions.
                const hasAll = permissions.length === $('input[name="permissions[]"]').length;
                $("#div-message")
                    .text(hasAll ? `${role} has all permissions.` : `Permissions of ${role}`)
                    .removeClass("alert-outline-warning")
                    .addClass("alert-outline-pink");

                $('#div-granted').show();
                loadPermissions();
            }

            function loadPermissions() {
                // Uncheck all
                $('input[name="permissions[]"]').prop('checked', false);

                // Check only those in the permissions array
                permissions.forEach(function (perm) {
                    $(`input[name="permissions[]"][value="${perm}"]`).prop('checked', true);
                });

                // Generate granted permissions table
                generateGrantedPermissionsTable();
            }

            function savePermissions(roleId, selectedPermissions) {
                $('#btn-save').prop('disabled', true);
                $.ajax({
                    url: "{{ route('permissions.role-permissions.update', ['role' => '__ID__']) }}".replace('__ID__', roleId),
                    method: "PUT",
                    data: { permissions : selectedPermissions },
                    dataType: "json"
                })
                .done(response => {
                    console.log('AJAX Success:', response);
                    permissions = selectedPermissions;
                    $('#btn-save').prop('disabled', false);
                    Swal.fire('Success!', response.message ,'success')
                        .then(() => {
                            showGrantedPermissions();
                            $('#tab-view').tab('show');
                        });
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                    $('#btn-save').prop('disabled', false);
                });
            }

            function generateGrantedPermissionsTable() {
                let $grantedBody = $('#tbl-granted tbody');
                $grantedBody.empty(); // Clear existing rows

                // Mapping table IDs to their section headings
                const sections = [
                    { id: 'tbl-masters', label: 'Masters', structured: true },
                    { id: 'tbl-transactions', label: 'Transactions', structured: true },
                    { id: 'tbl-data-explorer', label: 'Data Explorer', structured: false },
                    { id: 'tbl-reports', label: 'Reports', structured: false }
                ];

                sections.forEach(section => {
                    let $table = $('#' + section.id);
                    let checkedInputs = $table.find('input[name="permissions[]"]:checked');

                    // Skip section if nothing is checked
                    if (checkedInputs.length === 0) return;

                    // 1) Add main heading row
                    $grantedBody.append(`
                        <tr class="sub-head-row">
                            <th class="pl-2">${section.label}</th>
                            <td></td>
                        </tr>
                    `);

                    if (section.structured) {
                        // 2) Loop over each sub-head-row (Main Menu)
                        $table.find('tr.sub-head-row').each(function () {
                            let $mainMenuRow = $(this);
                            let $mainMenuCell = $mainMenuRow.find('th').first();
                            let mainMenuText = $mainMenuCell.text().trim();

                            // Get rows until next sub-head-row or end
                            let $rowsUntilNext = $mainMenuRow.nextUntil('tr.sub-head-row');
                            let hasAnyChecked = $rowsUntilNext.find('input[name="permissions[]"]:checked').length > 0;

                            if (!hasAnyChecked) return; // Skip if no selection in this main menu

                            // 3) Add Main Menu heading row
                            $grantedBody.append(`
                                <tr>
                                    <th class="pl-3">${mainMenuText}</th>
                                    <td></td>
                                </tr>
                            `);

                            // 4) Loop through sub-menu rows
                            $rowsUntilNext.each(function () {
                                let $subMenuRow = $(this);
                                let subMenuText = $subMenuRow.find('td').first().text().trim();

                                // Get all selected labels for this submenu
                                let labels = [];
                                $subMenuRow.find('input[name="permissions[]"]:checked').each(function () {
                                    labels.push($(this).next('label').text().trim());
                                });

                                if (labels.length > 0) {
                                    $grantedBody.append(`
                                        <tr>
                                            <td class="pl-4">${subMenuText}</td>
                                            <td class="pl-2">${labels.join(', ')}</td>
                                        </tr>
                                    `);
                                }
                            });
                        });
                    }
                    else {
                        // 5) Structure for Data Explorer / Reports
                        $table.find('tr').each(function () {
                            let $row = $(this);
                            let menuText = $row.find('td').first().text().trim();

                            let labels = [];
                            $row.find('input[name="permissions[]"]:checked').each(function () {
                                labels.push($(this).next('label').text().trim());
                            });

                            if (labels.length > 0) {
                                $grantedBody.append(`
                                    <tr>
                                        <td class="pl-3">${menuText}</td>
                                        <td class="pl-2">${labels.join(', ')}</td>
                                    </tr>
                                `);
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop