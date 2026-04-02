<div class="row px-3 py-2">
    <div class="col-12">
        <!-- Right-aligned nav tabs -->
        <ul class="nav nav-tabs justify-content-end" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-view" data-toggle="tab" href="#tab-permissions-view" role="tab">Granted Permissions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-update" data-toggle="tab" href="#tab-permissions-update" role="tab">Grant / Revoke</a>
            </li>
        </ul>
    </div>
</div>

<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane active" id="tab-permissions-view" role="tabpanel">
        <div class="text-center">
            <div id="div-message" class="alert alert-outline-warning py-2 mt-3 mb-0 d-inline-block" role="alert" style="min-width: 300px"></div>
        </div>

        <div id="div-granted" class="table-responsive p-3">
            <table id="tbl-granted" class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="text-left pl-2">Menu</th>
                        <th class="text-left pl-2">Permissions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane" id="tab-permissions-update" role="tabpanel">
        <div class="card mb-0">
            <div class="card-body pt-1">
                <div class="row">
                    <div class="col-sm-2 p-2">
                        <div class="nav flex-column nav-pills text-left" id="tab-permissions" role="tablist" aria-orientation="vertical">
                            <a class="nav-link waves-effect waves-light active" id="tab-menu-masters" data-toggle="pill" href="#tab-masters" role="tab" aria-controls="tab-masters" aria-selected="true">Masters</a>
                            <a class="nav-link waves-effect waves-light" id="tab-menu-transactions" data-toggle="pill" href="#tab-transactions" role="tab" aria-controls="tab-transactions" aria-selected="false">Transactions</a>
                            <a class="nav-link waves-effect waves-light" id="tab-menu-data-explorer" data-toggle="pill" href="#tab-data-explorer" role="tab" aria-controls="tab-data-explorer" aria-selected="false">Data Explorer</a>
                            <a class="nav-link waves-effect waves-light" id="tab-menu-reports" data-toggle="pill" href="#tab-reports" role="tab" aria-controls="tab-reports" aria-selected="false">Reports</a>
                        </div>
                    </div>
                    <div class="col-sm-10 p-2">
                        <div class="tab-content mo-mt-2" id="tab-permissions-contents">
                            <div class="tab-pane fade active show" id="tab-masters" role="tabpanel" aria-labelledby="tab-menu-masters">
                                <table id="tbl-masters" class="table table-bordered table-responsive table-sm w-100" style="overflow-x: auto">
                                    @foreach ($permissions['Masters'] as $mainMenu => $subMenus)
                                        <tr class="sub-head-row">
                                            <th class="pl-2 fixed-col-width">{{ $mainMenu }}</th>
                                            <th></th>
                                        </tr>
                                        @foreach ($subMenus as $subMenu => $permissionList)
                                            <tr>
                                                <td class="pl-4 fixed-col-width">{{ $subMenu }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center flex-wrap">
                                                        @foreach ($permissionList as $permKey => $permLabel)
                                                            <div class="checkbox mx-2">
                                                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}" id="chk-{{ $permKey }}">
                                                                <label for="chk-{{ $permKey }}">{{ $permLabel }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </table>
                            </div>

                            <div class="tab-pane fade" id="tab-transactions" role="tabpanel" aria-labelledby="tab-menu-transactions">
                                <table id="tbl-transactions" class="table table-bordered table-sm w-100">
                                    @foreach ($permissions['Transactions'] as $mainMenu => $subMenus)
                                        <tr class="sub-head-row">
                                            <th class="pl-2 fixed-col-width">{{ $mainMenu }}</th>
                                            <th></th>
                                        </tr>
                                        @foreach ($subMenus as $subMenu => $permissionList)
                                            <tr>
                                                <td class="pl-4 fixed-col-width">{{ $subMenu }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center flex-wrap">
                                                        @foreach ($permissionList as $permKey => $permLabel)
                                                            <div class="checkbox mx-2">
                                                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}" id="chk-{{ $permKey }}">
                                                                <label for="chk-{{ $permKey }}">{{ $permLabel }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </table>
                            </div>

                            <div class="tab-pane fade" id="tab-data-explorer" role="tabpanel" aria-labelledby="tab-menu-data-explorer">
                                <table id="tbl-data-explorer" class="table table-bordered table-sm w-100">
                                    @foreach ($permissions['Data Explorer'] as $menu => $options)
                                        <tr>
                                            <td class="pl-2 fixed-col-width sub-head-col">{{ $menu }}</td>
                                            <td>
                                                @foreach ($options as $permKey => $permLabel)
                                                    <div class="checkbox mx-2">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" id="chk-{{ $permKey }}">
                                                        <label for="chk-{{ $permKey }}">{{ $permLabel }}</label>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            <div class="tab-pane fade" id="tab-reports" role="tabpanel" aria-labelledby="tab-menu-reports">
                                <table id="tbl-reports" class="table table-bordered table-sm w-100">
                                    @foreach ($permissions['Reports'] as $menu => $options)
                                        <tr>
                                            <td class="pl-2 fixed-col-width sub-head-col">{{ $menu }}</td>
                                            <td>
                                                @foreach ($options as $permKey => $permLabel)
                                                    <div class="checkbox mx-2">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" id="chk-{{ $permKey }}">
                                                        <label for="chk-{{ $permKey }}">{{ $permLabel }}</label>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Button Bar -->
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <!-- Left side: Select/Clear -->
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="btn-select-all">Select All</button>
                        <button type="button" class="btn btn-outline-warning btn-sm" id="btn-clear-all">Clear All</button>
                    </div>

                    <!-- Right side: Reset/Update -->
                    <div>
                        <button type="reset" id="btn-reset" class="btn btn-secondary mr-3">Reset</button>
                        <button type="button" id="btn-save" class="btn btn-primary px-3">Save</button>
                    </div>
                </div>

            </div><!--end card-body-->
        </div><!--end card-->
    </div>
</div>