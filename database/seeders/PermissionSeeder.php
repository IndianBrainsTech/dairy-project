<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = $this->getPermissions();
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }

    private function getPermissions() {
        return [
            // Masters > Profiles > Customers
            'index_customer',
            'show_customer',
            'create_customer',
            'update_customer',
            'destroy_customer',
            'toggle_customer',
            'create_customer_address',
            'update_customer_address',
            'update_customer_shop_photo',
            'update_customer_profile_photo',

            // Masters > Profiles > Employees
            'index_employee',
            'show_employee',
            'create_employee',
            'update_employee',
            'toggle_employee',
            'update_employee_photo',

            // Masters > Products > Products
            'index_product',
            'show_product',
            'create_product',
            'update_product',
            'toggle_product',
            'reorder_product',

            // Masters > Products > Groups
            'index_product_group',
            'create_product_group',

            // Masters > Products > Units
            'index_unit',
            'create_unit',
            'update_unit',
            'destroy_unit',

            // Masters > Places > States
            'index_state',
            'create_state',
            'update_state',
            'destroy_state',

            // Masters > Places > Districts
            'index_district',
            'create_district',
            'update_district',

            // Masters > Places > Routes
            'index_route',
            'create_route',
            'update_route',

            // Masters > Places > Areas
            'index_area',
            'create_area',
            'update_area',
            'destroy_area',

            // Masters > Transport > Vehicles
            'index_vehicle',
            'create_vehicle',
            'update_vehicle',
            'destroy_vehicle',

            // Masters > Taxation > GST Master
            'index_gst_master',
            'create_gst_master',
            'update_gst_master',
            'destroy_gst_master',

            // Masters > Taxation > TCS Master
            'index_tcs_master',
            'create_tcs_master',

            // Masters > Taxation > TDS Master
            'index_tds_master',
            'create_tds_master',

            // Masters > Deals & Pricing > Price Master
            'index_price_master',
            'show_price_master',
            'create_price_master',
            'update_price_master',
            'toggle_price_master',
            'adjust_price_master',

            // Masters > Deals & Pricing > Discount Master
            'index_discount_master',
            'show_discount_master',
            'create_discount_master',
            'update_discount_master',
            'toggle_discount_master',

            // Masters > Deals & Pricing > Incentive Master
            'index_incentive_master',
            'show_incentive_master',
            'create_incentive_master',
            'update_incentive_master',
            'toggle_incentive_master',

            // Masters > Openings > Outstanding
            'index_outstanding',
            'update_outstanding',

            // Masters > Openings > Credit Limit
            'index_credit_limit',
            'update_credit_limit',

            // Masters > Openings > Turnover
            'index_turnover',
            'update_turnover',

            // Masters > Permissions > Roles
            'index_web_role',
            'create_web_role',
            'update_web_role',
            'destroy_web_role',

            // Masters > Permissions > Users
            'index_web_user',
            'create_web_user',
            'update_web_user',

            // Masters > Permissions > Role Permissions
            'show_role_permission',
            'update_role_permission',

            // Masters > Permissions > User Permissions
            'show_user_permission',
            'update_user_permission',

            // Masters > Expense Types
            'index_expense_type',
            'create_expense_type',
            'update_expense_type',
            'destroy_expense_type',

            // Masters > Bank Account
            'index_bank_account',
            'show_bank_account',
            'create_bank_account',
            'update_bank_account',

            // Masters > Settings
            'show_setting_invoice_number_format',
            'update_setting_invoice_number_format',
            'show_setting_receipt_date',
            'update_setting_receipt_date',

            // Transactions > Production > Stock Entry
            // 'index_stock_entry',
            // 'create_stock_entry',
            'create_stock',
            'index_stock',
            'show_stock',
            'update_stock',
            'cancel_stock',
            'approve_stock',

            // Transactions > Production > Current Stock
            'index_current_stock',

            // Transactions > Production > Closing Stock
            'index_closing_stock',

            // Transactions > Orders > Orders
            'create_order',
            'index_order',
            'show_order',
            'update_order',
            'cancel_order',
            'latest_order',
            'update_order_discount',

            // Transactions > Orders > Bulk Milk Orders
            'create_bulk_milk_order',
            'index_bulk_milk_order',
            'show_bulk_milk_order',
            'update_bulk_milk_order',
            'cancel_bulk_milk_order',

            // Transactions > Invoices > Invoices
            'make_invoice',
            'index_invoice',
            'show_invoice',
            'cancel_invoice',

            // Transactions > Invoices > Bulk Milk Invoices
            'make_bulk_milk_invoice',
            'index_bulk_milk_invoice',
            'show_bulk_milk_invoice',
            'cancel_bulk_milk_invoice',

            // Transactions > Sheets > Loading Sheet
            'show_loading_sheet',

            // Transactions > Sheets > Trip Sheet
            'show_trip_sheet',

            // Transactions > Job Work > Job Work
            'create_job_work',
            'index_job_work',
            'show_job_work',
            'update_job_work',
            'cancel_job_work',

            // Transactions > Job Work > Delivery Challan
            'make_delivery_challan',
            'index_delivery_challan',
            'show_delivery_challan',

            // Transactions > Receipts > Receipts
            'create_receipt',
            'index_receipt',
            'show_receipt',
            'update_receipt',
            'make_receipt',

            // Transactions > Receipts > Batch Denomination
            'index_batch_denomination',
            'create_batch_denomination',

            // Transactions > Credit Notes
            'create_credit_note',
            'index_credit_note',
            'show_credit_note',
            'update_credit_note',
            'cancel_credit_note',
            'approve_credit_note',

            // Transactions > Incentives > Incentives
            'create_incentive',
            'index_incentive',
            'show_incentive',
            'make_incentive',

            // Transactions > Incentives > Payout
            'create_payout_receipt',
            'create_payout_bank',

            // Transactions > Incentives > Approval
            'approve_payout_receipt',
            'approve_payout_bank',

            // Transactions > Incentives > Excel
            'index_incentive_excel',
            'show_incentive_excel',
            'download_incentive_excel',

            // Transactions > Diesel Bills > Entry
            'create_diesel_bill_entry',
            'index_diesel_bill_entry',
            'show_diesel_bill_entry',
            'accept_diesel_bill_entry',

            // Transactions > Diesel Bills > Generation
            'create_diesel_bill_statement',
            'index_diesel_bill_statement',
            'show_diesel_bill_statement',
            'accept_diesel_bill_statement',
            'cancel_diesel_bill_statement',

            // Transactions > Diesel Bills > Payment
            'show_diesel_bill_payment_request',
            'show_diesel_bill_payment_approve',

            // Transactions > Sales Return
            'create_sales_return',
            'index_sales_return',
            'show_sales_return',

            // Transactions > Denomination
            'index_receipt_denomination',
            'index_day_route_denomination',

            // Transactions > Expenses
            'index_expense_entry',
            'index_expense_approval',
            'index_expense_payment',
            'index_expense_slip',

            // Transactions > Tally
            'sync_tally_masters',
            'sync_tally_invoices',

            // Data Explorer > Products
            'index_price_table_explorer',
            'index_tax_table_explorer',

            // Data Explorer > Customers
            'index_gst_type_explorer',
            'index_tcs_status_explorer',
            'index_tds_status_explorer',
            'index_payment_mode_explorer',
            'index_incentive_mode_explorer',

            // Data Explorer > Price Master
            'index_price_list_explorer',
            'index_customer_price_explorer',
            'index_product_price_explorer',

            // Data Explorer > Stocks
            'show_current_stock',
            'show_stock_register',

            // Data Explorer
            'show_bank_payment',
            'show_cash_register',            

            // Reports > Sales Reports
            'show_item_wise_report',
            'show_hsn_wise_report',
            'show_tax_wise_report',
            'show_b2b_b2c_report',
            'show_zero_value_items_report',
            'show_item_wise_customer_report',
            'show_customer_wise_item_report',

            // Reports > Customer Reports
            'show_customer_statement_report',
            'show_customer_account_report',

            // Reports
            'show_invoice_report',
            'show_day_wise_report',
            'show_bill_wise_report',
            'show_transaction_report',
            'show_document_summary_report',
        ];
    }
}
