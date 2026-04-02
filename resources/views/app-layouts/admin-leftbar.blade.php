<!-- leftbar-tab-menu -->
<div class="leftbar-tab-menu">
    <div class="main-icon-menu">
        @php ($home_url = route('home'))
        <a href="{{ $home_url }}" class="logo logo-metrica d-block text-center">
            <span>
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="logo-small" class="logo-sm rounded-circle">
            </span>
        </a>
        <nav class="nav">
            <a href="#MenuDashboard" onclick="location.href='{{ $home_url }}'" class="nav-link button-menu-mobile" data-toggle="tooltip-custom" data-placement="right" title="" data-original-title="Dashboard" data-trigger="hover">
                <i data-feather="monitor" class="align-self-center menu-icon icon-dual"></i>
            </a>

            @canany(['index_customer', 'index_employee', 
                'index_product', 'index_product_group', 'index_unit',
                'index_state', 'index_district', 'index_route', 'index_area',
                'index_vehicle',
                'index_gst_master', 'index_tcs_master', 'index_tds_master',
                'index_price_master', 'index_discount_master', 'index_incentive_master',
                'index_outstanding', 'index_credit_limit', 'index_turnover',
                'index_web_role', 'index_web_user', 'show_role_permission', 'show_user_permission',
                'index_expense_type', 'index_bank_account', 'show_setting_invoice_number_format',
            ])
            <a href="#MenuMasters" class="nav-link" data-toggle="tooltip-custom" data-placement="right" title="" data-original-title="Masters" data-trigger="hover">
                <i data-feather="grid" class="align-self-center menu-icon icon-dual"></i>
            </a>
            @endcanany
            
            <a href="#MenuTransactions" class="nav-link" data-toggle="tooltip-custom" data-placement="right" title="" data-original-title="Transactions" data-trigger="hover">
                <i data-feather="package" class="align-self-center menu-icon icon-dual"></i>
            </a>

            <a href="#MenuExplorer" class="nav-link" data-toggle="tooltip-custom" data-placement="right" title="" data-original-title="Data Explorer" data-trigger="hover">
                <i data-feather="database" class="align-self-center menu-icon icon-dual"></i>
            </a>

            <a href="#MenuReports" class="nav-link" data-toggle="tooltip-custom" data-placement="right" title="" data-original-title="Reports" data-trigger="hover">
                <i data-feather="copy" class="align-self-center menu-icon icon-dual"></i>
            </a>

            {{-- <a href="#MenuTools" class="nav-link" data-toggle="tooltip-custom" data-placement="right" title="" data-original-title="Tools" data-trigger="hover">
                <i data-feather="lock" class="align-self-center menu-icon icon-dual"></i>
            </a> --}}
        </nav><!--end nav-->

        <div class="pro-metrica-end">
            <a href="{{ $home_url }}" class="profile">
                <img src="{{ asset('assets/images/users/user-avatar.jpg') }}" alt="profile-user" class="rounded-circle thumb-sm">
            </a>
        </div>
    </div><!--end main-icon-menu-->

    <div class="main-menu-inner">
        <!-- LOGO -->
        <div class="topbar-left">
            <a href="{{ $home_url }}" class="logo">
                <span>
                    <img src="{{ asset('assets/images/logo-dark.jpg') }}" alt="logo-large" class="logo-lg logo-dark">
                    <img src="{{ asset('assets/images/logo.jpg') }}" alt="logo-large" class="logo-lg logo-light">
                </span>
            </a>
        </div>
        <!--end logo-->

        <div class="menu-body slimscroll">
            <div id="MenuDashboard" class="main-icon-menu-pane">
                <div class="title-box">
                    <h6 class="menu-title">Dashboard</h6>
                </div>
            </div>

            {{------ Masters Menu Start -----}}            
            <div id="MenuMasters" class="main-icon-menu-pane">
                <div class="title-box">
                    <h6 class="menu-title">Masters</h6>
                </div>
                <ul class="nav metismenu">
                    @canany(['index_customer', 'index_employee'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Profiles</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('index_customer') <li><a href="{{ route('customers.index') }}">Customers</a></li> @endcan
                                @can('create_customer') <li><a href="{{ route('customers.create') }}" class="d-none">Add Customer</a></li> @endcan
                                @can('update_customer') <li><a href="{{ route('customers.edit') }}" class="d-none">Edit Customer</a></li> @endcan
                                @can('show_customer') <li><a href="{{ route('customers.show') }}" class="d-none">View Customer</a></li> @endcan
                                <li><a href="{{ route('customers.convert') }}" class="d-none">Convert Customer</a></li>
                                @can('index_employee') <li><a href="{{ route('employees.index') }}">Employees</a></li> @endcan
                                @can('create_employee') <li><a href="{{ route('employees.create') }}" class="d-none">Add Employee</a></li> @endcan
                                @can('update_employee') <li><a href="{{ route('employees.edit') }}" class="d-none">Edit Employee</a></li> @endcan
                                @can('show_employee') <li><a href="{{ route('employees.show') }}" class="d-none">View Employee</a></li> @endcan
                                {{-- <li><a href="{{ route('competitors.index') }}">Competitors</a></li> --}}
                                {{-- <li><a href="{{ route('roles.index') }}">Roles</a></li> --}}
                            </ul>
                        </li>
                    @endcanany

                    @canany(['index_product', 'index_product_group', 'index_unit'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Products</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('index_product') <li><a href="{{ route('products.index') }}">Products</a></li> @endcan
                                @can('create_product') <li><a href="{{ route('products.create') }}" class="d-none">Add Product</a></li> @endcan
                                @can('show_product') <li><a href="{{ route('products.show') }}" class="d-none">View Product</a></li> @endcan
                                @can('update_product') <li><a href="{{ route('products.edit') }}" class="d-none">Edit Product</a></li> @endcan
                                @can('index_product_group') <li><a href="{{ route('prgroups.index') }}">Groups</a></li> @endcan
                                @can('index_unit') <li><a href="{{ route('units.index') }}">Units</a></li> @endcan
                            </ul>
                        </li>
                    @endcanany

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);">
                            <span class="w-100">Purchase</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false" id="ul-purchase">
                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Items &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false" id="ul-purchase-items">
                                    <li id="li-purchase-items"><a href="{{ route('purchase.items.items.index') }}">Items</a></li>
                                    <li id="li-purchase-item-groups"><a href="{{ route('purchase.items.groups.index') }}">Groups</a></li>
                                    <li id="li-purchase-item-units"><a href="{{ route('purchase.items.units.index') }}">Units</a></li>
                                </ul>                                
                            </li>
                            <li id="li-suppliers">
                                <a href="{{ route('suppliers.index') }}">Suppliers</a>
                            </li>
                            <li>
                                <a href="{{ route('locations.index') }}">Locations</a>
                            </li>
                            <li>
                                <a href="{{ route('departments.index') }}">Departments</a>
                            </li>
                        </ul>
                        
                    </li>

                    @canany(['index_state', 'index_district', 'index_route', 'index_area'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Places</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('index_state') <li><a href="{{ route('states.index') }}">States</a></li> @endcan
                                @can('index_district') <li><a href="{{ route('districts.index') }}">Districts</a></li> @endcan
                                @can('index_route') <li><a href="{{ route('routes.index') }}">Routes</a></li> @endcan
                                @can('index_area') <li><a href="{{ route('areas.index') }}">Areas</a></li> @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['index_vehicle'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Transport</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('index_vehicle') <li><a href="{{ route('vehicles.index') }}">Vehicles</a></li> @endcan
                                <li><a href="{{ route('bunks.index') }}">Petrol Bunks</a></li>
                                <li><a href="{{ route('bunks.list') }}" class="d-none">Petrol Bunks</a></li>
                            </ul>
                        </li>
                    @endcanany

                    @canany(['index_gst_master', 'index_tcs_master', 'index_tds_master'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Taxation</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('index_gst_master') <li><a href="{{ route('gstmaster.index') }}">GST Master</a></li> @endcan
                                @can('index_tcs_master') <li><a href="{{ route('tcsmaster.index') }}">TCS Master</a></li> @endcan
                                @can('index_tds_master') <li><a href="{{ route('tdsmaster.index') }}">TDS Master</a></li> @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['index_price_master', 'index_discount_master', 'index_incentive_master'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Deals & Pricing</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false" id="ul-deals-pricing">
                                @can('index_price_master') <li id="li-price-master"><a href="{{ route('price-masters.index') }}">Price Masters</a></li> @endcan
                                {{-- @can('create_price_master') <li><a href="{{ route('price-master.create') }}" class="d-none">Add Price Master</a></li> @endcan --}}
                                {{-- @can('show_price_master') <li><a href="{{ route('price-master.show') }}" class="d-none">View Price Master</a></li> @endcan --}}
                                {{-- @can('update_price_master') <li><a href="{{ route('price-master.edit') }}" class="d-none">Edit Price Master</a></li> @endcan --}}
                                @can('index_discount_master') <li><a href="{{ route('discount-master.index') }}">Discount Master</a></li> @endcan
                                @can('create_discount_master') <li><a href="{{ route('discount-master.create') }}" class="d-none">Add Discount Master</a></li> @endcan
                                @can('show_discount_master') <li><a href="{{ route('discount-master.show') }}" class="d-none">View Discount Master</a></li> @endcan
                                @can('update_discount_master') <li><a href="{{ route('discount-master.edit') }}" class="d-none">Edit Discount Master</a></li> @endcan
                                @can('index_incentive_master') <li><a href="{{ route('incentive-master.index') }}">Incentive Master</a></li> @endcan
                                @can('create_incentive_master') <li><a href="{{ route('incentive-master.create') }}" class="d-none">Add Incentive Master</a></li> @endcan
                                @can('show_incentive_master') <li><a href="{{ route('incentive-master.show') }}" class="d-none">View Incentive Master</a></li> @endcan
                                @can('update_incentive_master') <li><a href="{{ route('incentive-master.edit') }}" class="d-none">Edit Incentive Master</a></li> @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['index_outstanding', 'index_credit_limit', 'index_turnover'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Openings</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('index_outstanding') <li><a href="{{ route('customers.outstanding') }}">Outstanding</a></li> @endcan
                                @can('index_credit_limit') <li><a href="{{ route('customers.credit.limit') }}">Credit Limit</a></li> @endcan
                                @can('index_turnover') <li><a href="{{ route('customers.turnover') }}">Turnover</a></li> @endcan                                
                                {{-- <li><a href="{{ route('openings.cash') }}">Cash</a></li> --}}
                                <li><a href="{{ route('bunks.turnover.index') }}">Petrol Bunk</a>
                            </ul>
                        </li>
                    @endcanany

                    @canany(['index_web_role', 'index_web_user', 'show_role_permission', 'show_user_permission'])
                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Permissions</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('index_web_role') <li><a href="{{ route('permissions.roles.index') }}">Roles</a></li> @endcan
                            @can('index_web_user') <li><a href="{{ route('permissions.users.index') }}">Users</a></li> @endcan
                            @can('show_role_permission') <li><a href="{{ route('permissions.role-permissions.index') }}">Role Permissions</a></li> @endcan
                            @can('show_user_permission') <li><a href="{{ route('permissions.user-permissions.index') }}">User Permissions</a></li> @endcan
                        </ul>
                    </li>
                    @endcanany

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Banks</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="{{ route('banks.index') }}">Banks</a></li>
                            <li><a href="{{ route('banks.branches.index') }}">Branches</a></li>
                        </ul>
                    </li>

                    @can('index_expense_type') <li class="nav-item"><a class="nav-link" href="{{ route('expense.types') }}">Expense Types</a></li> @endcan
                    @can('index_bank_account') <li class="nav-item"><a class="nav-link" href="{{ route('bank-account.index') }}">Bank Account</a></li> @endcan
                    {{-- <li class="nav-item"><a class="nav-link" href="{{ route('admin.mobile_security') }}">Mobile Security</a></li> --}}
                    @can('show_setting_invoice_number_format') <li class="nav-item"><a class="nav-link" href="{{ route('master.settings') }}">Settings</a></li> @endcan
                    @can('show_setting_receipt_date') <li class="nav-item"><a class="nav-link" href="{{ route('settings.date.create') }}">Date Settings</a></li> @endcan
                </ul>
            </div>
            {{-- Masters Menu End --}}

            {{-- Transactions Menu Start --}}
            <div id="MenuTransactions" class="main-icon-menu-pane">
                <div class="title-box">
                    <h6 class="menu-title">Transactions</h6>
                </div>
                <ul class="nav metismenu">
                    {{-- <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Enquiry</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="{{ route('enquiries.index') }}">Enquiry</a></li>
                            <li><a href="{{ route('followups.index') }}">Followup</a></li>
                            <li><a href="{{ route('conversions.index') }}">Shop Conversion</a></li>
                            <li><a href="{{ route('enquiries.show') }}" class="d-none">View Enquiry</a></li>
                            <li><a href="{{ route('followups.show') }}" class="d-none">View Followup</a></li>
                            <li><a href="{{ route('dayroute.show') }}" class="d-none">Day Route</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('attendances.index') }}">Attendance</a></li> --}}

                    @canany(['create_stock', 'index_stock', 'show_stock', 'approve_stock'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Stocks</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('create_stock') <li><a href="{{ route('stocks.create') }}">Create Stock</a></li> @endcan
                                @can('index_stock') <li><a href="{{ route('stocks.index') }}">View Stocks</a></li> @endcan
                                @can('show_stock') <li><a href="{{ route('stocks.show') }}" class="d-none">View Stock</a></li> @endcan
                                @can('approve_stock') <li><a href="{{ route('stocks.approval.index') }}">Approve Stocks</a></li> @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Production</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('create_stock_entry') <li><a href="{{ route('stock.entry') }}" style="display:none">Stock Entry</a></li> @endcan
                            @can('create_stock_entry') <li><a href="{{ route('entry.edit') }}" style="display:none">Stock Edit</a></li> @endcan
                            @can('index_stock_entry') <li><a href="{{ route('stock.listview') }}">Stock Entry</a></li> @endcan
                            @can('index_stock_entry') <li><a href="{{ route('stock.show') }}"  style="display:none">View Stock</a></li> @endcan
                            @can('index_current_stock') <li><a href="{{ route('current.stock') }}">Current Stock</a></li> @endcan
                            @can('index_closing_stock') <li><a href="{{ route('closing.stock') }}">Closing Stock</a></li> @endcan
                        </ul>
                    </li> --}}

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Orders</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('create_order') <li><a href="{{ route('orders.create') }}">Place Order</a></li> @endcan
                            @can('index_order') <li><a href="{{ route('orders.index') }}">View Orders</a></li> @endcan
                            @can('show_order') <li><a href="{{ route('orders.show') }}" class="d-none">View Order</a></li> @endcan
                            @can('create_bulk_milk_order') <li><a href="{{ route('bulk-milk.orders.create') }}">Place Bulk Milk</a></li> @endcan
                            @can('index_bulk_milk_order') <li><a href="{{ route('bulk-milk.orders.index') }}">View Bulk Milk</a></li> @endcan
                            @can('show_bulk_milk_order') <li><a href="{{ route('bulk-milk.orders.show') }}" style="display:none">View Bulk Milk</a></li> @endcan
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Invoices</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('make_invoice') <li><a href="{{ route('invoices.create') }}">Make Invoices</a></li> @endcan
                            @can('index_invoice') <li><a href="{{ route('invoices.index') }}">List Invoices</a></li> @endcan
                            @can('show_invoice') <li><a href="{{ route('invoices.show') }}" class="d-none">View Invoice</a></li> @endcan
                            @can('cancel_invoice') <li><a href="{{ route('invoices.cancel.load') }}">Cancel Invoice</a></li> @endcan
                            @can('cancel_invoice') <li><a href="{{ route('invoices.cancel.show') }}" class="d-none">Cancel Invoice</a></li> @endcan
                            @can('make_bulk_milk_invoice') <li><a href="{{ route('bulk-milk.invoices.create') }}">Make Bulk Milk Invoices</a></li> @endcan
                            @can('index_bulk_milk_invoice') <li><a href="{{ route('bulk-milk.invoices.index') }}">Bulk Milk Invoices</a></li> @endcan
                            @can('show_bulk_milk_invoice') <li><a href="{{ route('bulk-milk.invoices.show') }}" class="d-none">View Bulk Milk Invoice</a></li> @endcan
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Sheets</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('show_loading_sheet') <li><a href="{{ route('sheets.loading-sheet') }}">Loading Sheet</a></li> @endcan
                            @can('show_trip_sheet') <li><a href="{{ route('sheets.trip-sheet') }}">Trip Sheet</a></li> @endcan
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Job Work</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('create_job_work') <li><a href="{{ route('job-work.create') }}">Create Job Work</a></li> @endcan
                            @can('index_job_work') <li><a href="{{ route('job-work.index') }}">View Job Work</a></li> @endcan
                            @can('show_job_work') <li><a href="{{ route('job-work.show') }}" style="display:none">View Job Work</a></li> @endcan
                            @can('make_delivery_challan') <li><a href="{{ route('delivery-challan.create') }}">Make Delivery Challan</a></li> @endcan
                            @can('index_delivery_challan') <li><a href="{{ route('delivery-challan.index') }}">Delivery Challans</a></li> @endcan
                            @can('show_delivery_challan') <li><a href="{{ route('delivery-challan.show') }}" class="d-none">View Delivery Challan</a></li> @endcan
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Receipts</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('create_receipt') <li><a href="{{ route('receipts.create') }}">Create Receipt</a></li> @endcan
                            @can('index_receipt') <li><a href="{{ route('receipts.index') }}">View Receipts</a></li> @endcan
                            @can('show_receipt') <li><a href="{{ route('receipts.show') }}" class="d-none">View Receipt</a></li> @endcan
                            @can('update_receipt') <li><a href="{{ route('receipts.edit') }}" class="d-none">Edit Receipt</a></li> @endcan
                            @can('create_batch_denomination') <li><a href="{{ route('receipts.batch-denomination.create') }}">Batch Denomination</a></li> @endcan
                            @can('index_batch_denomination') <li><a href="{{ route('receipts.make.index') }}">Make Receipts</a></li> @endcan
                            @can('index_batch_denomination') <li><a href="{{ route('receipts.make.show') }}" class="d-none">Make Receipt</a></li> @endcan
                        </ul>
                    </li>

                    @can('index_sales_return') <li class="nav-item"><a class="nav-link" href="{{ route('sales-return.index') }}">Sales Return</a></li> @endcan
                    @can('create_sales_return') <li class="nav-item"><a href="{{ route('sales-return.create') }}" class="d-none">Sales Return</a></li> @endcan
                    @can('show_sales_return') <li class="nav-item"><a href="{{ route('sales-return.show') }}" class="d-none">View Sales Return</a></li> @endcan

                    @canany(['create_credit_note', 'index_credit_note', 'approve_credit_note'])
                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Credit Notes</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false" id="ul-credit-notes">
                            @can('create_credit_note') <li id="li-credit-notes-create"><a href="{{ route('credit-notes.create') }}">Create</a></li> @endcan
                            @can('index_credit_note') <li id="li-credit-notes-list"><a href="{{ route('credit-notes.index') }}">List</a></li> @endcan
                            @can('approve_credit_note') <li><a href="{{ route('credit-notes.approve.create') }}">Approve</a></li> @endcan
                        </ul>
                    </li>
                    @endcanany

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Denomination</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('index_receipt_denomination') <li><a href="{{ route('receipt.denomination') }}">Receipt</a></li> @endcan
                            @can('index_receipt_denomination') <li><a href="{{ route('receipt.denomination.view') }}" style="display:none">Receipt</a></li> @endcan
                            @can('index_day_route_denomination') <li><a href="{{ route('day.route.denomination') }}">Day Route</a></li> @endcan
                            @can('index_day_route_denomination') <li><a href="{{ route('route.denomination.view') }}" style="display:none">Day Route</a></li> @endcan
                            @can('index_day_route_denomination') <li><a href="{{ route('day.denomination.view') }}" style="display:none">Day Route</a></li> @endcan
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);">
                            <span class="w-100">Incentives</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('create_incentive') <li><a href="{{ route('incentives.create') }}">Create Incentive</a></li> @endcan
                            @can('index_incentive') <li><a href="{{ route('incentives.index') }}">List Incentives</a></li> @endcan
                            @can('show_incentive') <li><a href="{{ route('incentives.show') }}" class="d-none">View Incentive</a></li> @endcan
                            @can('make_incentive') <li><a href="{{ route('incentives.make') }}">Make Incentives</a></li> @endcan

                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Payouts &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false">
                                    @can('create_payout_receipt') <li><a href="{{ route('incentives.payouts.receipt.create') }}">Receipt</a></li> @endcan
                                    @can('create_payout_bank') <li><a href="{{ route('incentives.payouts.bank.create') }}">Bank</a></li> @endcan
                                    <!-- <li><a href="#">View</a></li> -->
                                </ul>
                            </li>

                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Approval &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false">
                                    @can('approve_payout_receipt') <li><a href="#">Receipt</a></li> @endcan
                                    @can('approve_payout_bank') <li><a href="{{ route('incentives.payouts.bank.approve') }}">Bank</a></li> @endcan
                                </ul>
                            </li>

                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Records &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false">
                                    <!-- <li><a href="#">Payments</a></li> -->
                                    @can('index_incentive_excel') <li><a href="{{ route('incentives.records.excel.index') }}">Excel</a></li> @endcan
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);">
                            <span class="w-100">Diesel Bills</span>
                            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Entry &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false">
                                    @can('create_diesel_bill_entry') <li><a href="{{ route('diesel-bills.entries.create') }}">Create</a></li> @endcan
                                    @can('index_diesel_bill_entry') <li><a href="{{ route('diesel-bills.entries.index') }}">List</a></li> @endcan
                                    @can('accept_diesel_bill_entry') <li><a href="{{ route('diesel-bills.entries.accept.index') }}">Accept</a></li> @endcan
                                </ul>
                            </li>

                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Generation &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false">
                                    @can('create_diesel_bill_statement') <li><a href="{{ route('diesel-bills.statements.create') }}">Create</a></li> @endcan
                                    @can('index_diesel_bill_statement') <li><a href="{{ route('diesel-bills.statements.index') }}">List</a></li> @endcan
                                    @can('accept_diesel_bill_statement') <li><a href="{{ route('diesel-bills.statements.accept.index') }}">Accept</a></li> @endcan
                                </ul>
                            </li>

                            <li>
                                <a href="javascript:void(0);">
                                    <span class="w-100">Payment &nbsp; </span>
                                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                                </a>
                                <ul class="nav-second-level pl-3" aria-expanded="false">
                                    @can('show_diesel_bill_payment_request') <li><a href="{{ route('diesel-bills.payments.request.create') }}">Request</a></li> @endcan
                                    @can('show_diesel_bill_payment_approve') <li><a href="{{ route('diesel-bills.payments.approve.create') }}">Approve</a></li> @endcan                                    
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Expenses</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('index_expense_entry') <li><a href="{{ route('expense.entry.list') }}">Expense Entry</a></li> @endcan
                            @can('index_expense_entry') <li><a href="{{ route('expense.entry') }}" style="display:none">Expense Entry</a></li> @endcan
                            @can('index_expense_approval') <li><a href="{{ route('expense.approval') }}">Expense Approval</a></li> @endcan
                            @can('index_expense_payment') <li><a href="{{ route('expense.payment') }}">Expense Payment</a></li> @endcan
                            @can('index_expense_slip') <li><a href="{{ route('expense.receipt.list') }}">Expense Slip</a></li> @endcan
                            @can('index_expense_entry') <li><a href="{{ route('expense.receipt.view') }}" style="display:none">Expense Receipt view</a></li> @endcan
                            @can('index_expense_entry') <li><a href="{{ route('expense.entry.view') }}" style="display:none">Expense Entry view</a></li> @endcan
                            @can('index_expense_entry') <li><a href="{{ route('expense.entry.edit') }}" style="display:none">Expense Entry Edit</a></li> @endcan
                            @can('index_expense_entry') <li><a href="{{ route('expense.entry.store') }}" style="display:none">Expense Entry Store</a></li> @endcan
                            {{-- <li><a href="{{ route('closing.balance') }}">Closing Balance</a></li> --}}
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Tally</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            {{-- @can('index_expense_entry') <li><a href="{{ route('tally.sync') }}">Tally Sync</a></li> @endcan --}}
                            @can('sync_tally_masters') <li><a href="{{ route('tally.masters') }}">Sync Masters</a></li> @endcan
                            @can('sync_tally_invoices') <li><a href="{{ route('tally.invoices') }}">Sync Invoices</a></li> @endcan
                        </ul>
                    </li>
                </ul>
            </div>
            {{-- Transactions Menu End --}}

            {{-- Explorer Menu Start --}}
            <div id="MenuExplorer" class="main-icon-menu-pane">
                <div class="title-box">
                    <h6 class="menu-title">Explorer</h6>
                </div>
                <ul class="nav metismenu">
                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Products</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('index_price_table_explorer') <li><a href="{{ route('price.explorer') }}">Price Table</a></li> @endcan
                            @can('index_tax_table_explorer') <li><a href="{{ route('tax.explorer') }}">Tax Table</a></li> @endcan
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Customers</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('index_gst_type_explorer') <li><a href="{{ route('gst.explorer') }}">GST Type</a></li> @endcan
                            @can('index_tcs_status_explorer') <li><a href="{{ route('tcs.explorer') }}">TCS Status</a></li> @endcan
                            @can('index_tds_status_explorer') <li><a href="{{ route('tds.explorer') }}">TDS Status</a></li> @endcan
                            @can('index_payment_mode_explorer') <li><a href="{{ route('payment.explorer') }}">Payment Mode</a></li> @endcan
                            @can('index_incentive_mode_explorer') <li><a href="{{ route('incentive.explorer') }}">Incentive Mode</a></li> @endcan
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Price Master</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('index_price_list_explorer') <li><a href="{{ route('price.list') }}">Price List</a></li> @endcan
                            @can('index_customer_price_explorer') <li><a href="{{ route('customer.price') }}">Customers</a></li> @endcan
                            @can('index_product_price_explorer') <li><a href="{{ route('price.variant') }}">Products</a></li> @endcan
                        </ul>
                    </li>
                    @canany(['show_current_stock', 'show_stock_register'])
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);"><span class="w-100">Stocks</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                @can('show_current_stock') <li><a href="{{ route('stocks.current') }}">Current Stock</a></li> @endcan
                                @can('show_stock_register') <li><a href="{{ route('stocks.register') }}">Stock Register</a></li> @endcan                           
                            </ul>
                        </li>
                    @endcanany
                    @can('show_bank_payment')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('payments.index') }}">Bank Payments</a>
                    </li>
                    @endcan
                    @can('show_cash_register')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cash.register.') }}">Cash Register</a>
                    </li>
                    @endcan
                </ul>
            </div>
            {{-- Explorer Menu End --}}

            {{-- Report Menu Start --}}
            <div id="MenuReports" class="main-icon-menu-pane">
                <div class="title-box">
                    <h6 class="menu-title">Reports</h6>
                </div>
                <ul class="nav metismenu">
                    {{-- <li class="nav-item"><a class="nav-link" href="{{ route('report.enquiry') }}">Enquiry</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('report.attendance') }}">Attendance</a></li> --}}

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Sales Reports</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('show_item_wise_report') <li><a href="{{ route('report.sales.item-wise') }}">Item wise Report</a></li> @endcan
                            @can('show_hsn_wise_report') <li><a href="{{ route('report.sales.hsn-wise') }}">HSN wise Report</a></li> @endcan
                            @can('show_tax_wise_report') <li><a href="{{ route('report.sales.tax-wise') }}">Tax wise Report</a></li> @endcan
                            @can('show_b2b_b2c_report') <li><a href="{{ route('reports.sales.business-wise') }}">B2B    / B2C Report</a></li> @endcan
                            @can('show_zero_value_items_report') <li><a href="{{ route('reports.sales.zero-value') }}">Zero Value Items</a></li> @endcan
                            @can('show_item_wise_customer_report') <li><a href="{{ route('report.customer.item-wise') }}">Item wise Customer Report</a></li> @endcan
                            @can('show_customer_wise_item_report') <li><a href="{{ route('report.item.customer-wise') }}">Customer wise Item Report</a></li> @endcan
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript: void(0);"><span class="w-100">Customer Reports</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                        <ul class="nav-second-level" aria-expanded="false">
                            @can('show_customer_statement_report') <li><a href="{{ route('report.customer.statement') }}">Customer Statement</a></li> @endcan
                            @can('show_customer_account_report') <li><a href="{{ route('report.customer.account') }}">Customer Accounts</a></li> @endcan
                        </ul>
                    </li>

                    @can('show_invoice_report') <li class="nav-item"><a class="nav-link" href="{{ route('report.invoice') }}">Invoice Report</a></li> @endcan
                    @can('show_day_wise_report') <li class="nav-item"><a class="nav-link" href="{{ route('report.day-wise') }}">Day wise Report</a></li> @endcan
                    @can('show_bill_wise_report') <li class="nav-item"><a class="nav-link" href="{{ route('reports.bill-wise') }}">Bill wise Report</a></li> @endcan
                    @can('show_transaction_report') <li class="nav-item"><a class="nav-link" href="{{ route('report.transaction') }}">Transaction Report</a></li> @endcan
                    @can('show_document_summary_report') <li class="nav-item"><a class="nav-link" href="{{ route('reports.document') }}">Document Summary</a></li> @endcan
                </ul>
            </div>
            {{-- Report Menu Start --}}

            {{-- Tools Menu Start --}}
            <div id="MenuTools" class="main-icon-menu-pane">
                <div class="title-box">
                    <h6 class="menu-title">Tools</h6>
                </div>
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link" href="/tools/backup_db">Database</a></li>
                    <li class="nav-item"><a class="nav-link" href="/test">Test Page</a></li>
                </ul>
            </div>
            {{-- Tools Menu End --}}

        </div><!--end menu-body-->
    </div><!-- end main-menu-inner-->
</div>
<!-- end leftbar-tab-menu-->