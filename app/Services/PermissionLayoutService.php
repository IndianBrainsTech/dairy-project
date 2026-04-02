<?php

namespace App\Services;

class PermissionLayoutService
{
    /**
     * Returns the structured permission layout grouped by tabs and menus.
     *
     * @return array<string, array<string, array<string, array<string, string>>>>
     */
    public function getAllPermissions(): array
    {
        $permissions = [
            'Masters' => [
                'Profiles' => [
                    'Customers' => [
                        'index_customer'                =>  'List',
                        'show_customer'                 =>  'View',
                        'create_customer'               =>  'Add',
                        'update_customer'               =>  'Edit',
                        'destroy_customer'              =>  'Delete',
                        'toggle_customer'               =>  'Enable/Disable',
                        'create_customer_address'       =>  'Add Address',
                        'update_customer_address'       =>  'Edit Address',
                        'update_customer_shop_photo'    =>  'Edit Shop Photo',
                        'update_customer_profile_photo' =>  'Edit Profile Photo',
                    ],
                    'Employees' => [
                        'index_employee'                =>  'List',
                        'show_employee'                 =>  'View',
                        'create_employee'               =>  'Add',
                        'update_employee'               =>  'Edit',
                        'toggle_employee'               =>  'Enable/Disable',
                        'update_employee_photo'         =>  'Edit Photo',
                    ],
                ],
                'Products' => [
                    'Products' => [
                        'index_product'                 =>  'List',
                        'show_product'                  =>  'View',
                        'create_product'                =>  'Add',
                        'update_product'                =>  'Edit',
                        'toggle_product'                =>  'Enable/Disable',
                        'reorder_product'               =>  'Reorder',
                    ],
                    'Groups' => [
                        'index_product_group'           =>  'List',
                        'create_product_group'          =>  'Add',
                    ],
                    'Units' => [
                        'index_unit'                    =>  'List',
                        'create_unit'                   =>  'Add',
                        'update_unit'                   =>  'Edit',
                        'destroy_unit'                  =>  'Delete',
                    ],
                ],

                'Purchase' => [
                    'Items' => [
                        'index_product'                 =>  'List',
                        'show_product'                  =>  'View',
                        'create_product'                =>  'Add',
                        'update_product'                =>  'Edit',
                        'toggle_product'                =>  'Enable/Disable',
                        'reorder_product'               =>  'Reorder',
                    ],
                ],

                'Places' => [
                    'States' => [
                        'index_state'                   =>  'List',
                        'create_state'                  =>  'Add',
                        'update_state'                  =>  'Edit',
                        'destroy_state'                 =>  'Delete',
                    ],
                    'Districts' => [
                        'index_district'                =>  'List',
                        'create_district'               =>  'Add',
                        'update_district'               =>  'Edit',
                    ],
                    'Routes' => [
                        'index_route'                   =>  'List',
                        'create_route'                  =>  'Add',
                        'update_route'                  =>  'Edit',
                    ],
                    'Areas' => [
                        'index_area'                    =>  'List',
                        'create_area'                   =>  'Add',
                        'update_area'                   =>  'Edit',
                        'destroy_area'                  =>  'Delete',
                    ],
                ],

                'Transport' => [
                    'Vehicles' => [
                        'index_vehicle'                 =>  'List',
                        'create_vehicle'                =>  'Add',
                        'update_vehicle'                =>  'Edit',
                        'destroy_vehicle'               =>  'Delete',
                    ],
                ],

                'Taxation' => [
                    'GST Master' => [
                        'index_gst_master'              =>  'List',
                        'create_gst_master'             =>  'Add',
                        'update_gst_master'             =>  'Edit',
                        'destroy_gst_master'            =>  'Delete',
                    ],
                    'TCS Master' => [
                        'index_tcs_master'              =>  'List',
                        'create_tcs_master'             =>  'New',
                    ],
                    'TDS Master' => [
                        'index_tds_master'              =>  'List',
                        'create_tds_master'             =>  'New',
                    ],
                ],

                'Deals & Pricing' => [
                    'Price Master' => [
                        'index_price_master'           =>  'List',
                        'show_price_master'            =>  'View',
                        'create_price_master'          =>  'Add',
                        'update_price_master'          =>  'Edit',
                        'toggle_price_master'          =>  'Enable/Disable',
                        'adjust_price_master'          =>  'Adjust',
                    ],
                    'Discount Master' => [
                        'index_discount_master'        =>  'List',
                        'show_discount_master'         =>  'View',
                        'create_discount_master'       =>  'Add',
                        'update_discount_master'       =>  'Edit',
                        'toggle_discount_master'       =>  'Enable/Disable',
                    ],
                    'Incentive Master' => [
                        'index_incentive_master'       =>  'List',
                        'show_incentive_master'        =>  'View',
                        'create_incentive_master'      =>  'Add',
                        'update_incentive_master'      =>  'Edit',
                        'toggle_incentive_master'      =>  'Enable/Disable',
                    ],
                ],

                'Openings' => [
                    'Outstanding' => [
                        'index_outstanding'            =>  'List',
                        'update_outstanding'           =>  'Edit',
                    ],
                    'Credit Limit' => [
                        'index_credit_limit'           =>  'List',
                        'update_credit_limit'          =>  'Edit',
                    ],
                    'Turnover' => [
                        'index_turnover'               =>  'List',
                        'update_turnover'              =>  'Edit',
                    ],
                ],

                'Permissions' => [
                    'Roles' => [
                        'index_web_role'               =>  'List',
                        'create_web_role'              =>  'Add',
                        'update_web_role'              =>  'Edit',
                        'destroy_web_role'             =>  'Delete',
                    ],
                    'Users' => [
                        'index_web_user'               =>  'List',
                        'create_web_user'              =>  'Add',
                        'update_web_user'              =>  'Edit',
                    ],
                    'Role Permissions' => [
                        'show_role_permission'         =>  'Load',
                        'update_role_permission'       =>  'Save',
                    ],
                    'User Permissions' => [
                        'show_user_permission'         =>  'Load',
                        'update_user_permission'       =>  'Save',
                    ],
                ],

                'Settings' => [
                    'Invoice Number Format' => [
                        'show_setting_invoice_number_format'   =>  'View',
                        'update_setting_invoice_number_format' =>  'Edit',
                    ],
                    'Receipt Date Control' => [
                        'show_setting_receipt_date'    =>  'View',
                        'update_setting_receipt_date'  =>  'Edit',
                    ],
                ],

                'Masters' => [
                    'Expense Types' => [
                        'index_expense_type'           =>  'List',
                        'create_expense_type'          =>  'Add',
                        'update_expense_type'          =>  'Edit',
                        'destroy_expense_type'         =>  'Delete',
                    ],
                    'Bank Account' => [
                        'index_bank_account'           =>  'List',
                        'show_bank_account'            =>  'View',
                        'create_bank_account'          =>  'Add',
                        'update_bank_account'          =>  'Edit',
                    ],                    
                ],
            ],

            'Transactions' => [
                // 'Production' => [
                //     'Stock Entry' => [
                //         'index_stock_entry'             =>  'List',
                //         'create_stock_entry'            =>  'Add',
                //     ],
                //     'Current Stock' => [
                //         'index_current_stock'           =>  'List',
                //     ],
                //     'Closing Stock' => [
                //         'index_closing_stock'           =>  'List',
                //     ],
                // ],
                'Stocks' => [
                    'Stocks' => [
                        'create_stock'                  =>  'Create',
                        'index_stock'                   =>  'List',
                        'show_stock'                    =>  'View',
                        'update_stock'                  =>  'Edit',
                        'cancel_stock'                  =>  'Cancel',
                        'approve_stock'                 =>  'Approve',
                    ],
                ],

                'Orders' => [
                    'Orders' => [
                        'create_order'                  =>  'Place',
                        'index_order'                   =>  'List',
                        'show_order'                    =>  'View',
                        'update_order'                  =>  'Edit',
                        'cancel_order'                  =>  'Cancel',
                        'latest_order'                  =>  'Yesterday Order',
                        'update_order_discount'         =>  'Edit Discount',
                    ],
                    'Bulk Milk Orders' => [
                        'create_bulk_milk_order'        =>  'Place',
                        'index_bulk_milk_order'         =>  'List',
                        'show_bulk_milk_order'          =>  'View',
                        'update_bulk_milk_order'        =>  'Edit',
                        'cancel_bulk_milk_order'        =>  'Cancel',
                    ],
                ],

                'Invoices' => [
                    'Invoices' => [
                        'make_invoice'                  =>  'Make',
                        'index_invoice'                 =>  'List',
                        'show_invoice'                  =>  'View',
                        'cancel_invoice'                =>  'Cancel',
                    ],
                    'Bulk Milk Invoices' => [
                        'make_bulk_milk_invoice'        =>  'Make',
                        'index_bulk_milk_invoice'       =>  'List',
                        'show_bulk_milk_invoice'        =>  'View',
                        'cancel_bulk_milk_invoice'      =>  'Cancel',
                    ],
                ],

                'Sheets' => [
                    'Loading Sheet' => [
                        'show_loading_sheet'            =>  'View',
                    ],
                    'Trip Sheet' => [
                        'show_trip_sheet'               =>  'View',
                    ],
                ],

                'Job Work' => [
                    'Job Work' => [
                        'create_job_work'               =>  'Create',
                        'index_job_work'                =>  'List',
                        'show_job_work'                 =>  'View',
                        'update_job_work'               =>  'Edit',
                        'cancel_job_work'               =>  'Cancel',
                    ],
                    'Delivery Challan' => [
                        'make_delivery_challan'         =>  'Make',
                        'index_delivery_challan'        =>  'List',
                        'show_delivery_challan'         =>  'View',
                    ],
                ],

                'Receipts' => [
                    'Receipt' => [
                        'create_receipt'                =>  'Create',
                        'index_receipt'                 =>  'List',
                        'show_receipt'                  =>  'View',
                        'update_receipt'                =>  'Edit',
                        'make_receipt'                  =>  'Make',
                    ],
                    'Batch Denomination' => [
                        'index_batch_denomination'      =>  'List',
                        'create_batch_denomination'     =>  'Create',
                    ],
                ],

                'Credit Notes' => [
                    'Credit Notes' => [
                        'create_credit_note'            =>  'Create',
                        'index_credit_note'             =>  'List',
                        'show_credit_note'              =>  'View',
                        'update_credit_note'            =>  'Edit',
                        'cancel_credit_note'            =>  'Cancel',
                        'approve_credit_note'           =>  'Approve',
                    ],
                ],

                'Incentives' => [
                    'Incentive' => [
                        'create_incentive'              =>  'Create',
                        'index_incentive'               =>  'List',
                        'show_incentive'                =>  'View',
                        'make_incentive'                =>  'Make',
                    ],
                    'Payout' => [
                        'create_payout_receipt'         =>  'Receipt',
                        'create_payout_bank'            =>  'Bank',
                    ],
                    'Approval' => [
                        'approve_payout_receipt'        =>  'Receipt',
                        'approve_payout_bank'           =>  'Bank',
                    ],
                    'Excel' => [
                        'index_incentive_excel'         =>  'List',
                        'show_incentive_excel'          =>  'View',
                        'download_incentive_excel'      =>  'Download',
                    ],
                ],

                'Diesel Bills' => [
                    'Entry' => [
                        'create_diesel_bill_entry'      =>  'Create',
                        'index_diesel_bill_entry'       =>  'List',
                        'show_diesel_bill_entry'        =>  'View',
                        'accept_diesel_bill_entry'      =>  'Accept',
                    ],
                    'Generation' => [
                        'create_diesel_bill_statement'  =>  'Create',
                        'index_diesel_bill_statement'   =>  'List',
                        'show_diesel_bill_statement'    =>  'View',
                        'accept_diesel_bill_statement'  =>  'Accept',
                        'cancel_diesel_bill_statement'  =>  'Cancel',
                    ],
                    'Payment' => [
                        'show_diesel_bill_payment_request' =>  'Request',
                        'show_diesel_bill_payment_approve' =>  'Approve',                        
                    ],
                ],

                'Transactions' => [
                    'Sales Return' => [
                        'create_sales_return'           =>  'Create',
                        'index_sales_return'            =>  'List',
                        'show_sales_return'             =>  'View',
                    ],
                    'Denomination' => [
                        'index_receipt_denomination'    =>  'Receipt',
                        'index_day_route_denomination'  =>  'Day Route',
                    ],
                    'Expenses' => [
                        'index_expense_entry'           =>  'Entry',
                        'index_expense_approval'        =>  'Approval',
                        'index_expense_payment'         =>  'Payment',
                        'index_expense_slip'            =>  'Slip',
                    ],
                    'Tally' => [
                        'sync_tally_masters'            =>  'Sync Masters',
                        'sync_tally_invoices'           =>  'Sync Invoices',
                    ],
                ],
            ],

            'Data Explorer' => [
                'Products' => [
                    'index_price_table_explorer'        =>  'Price Table',
                    'index_tax_table_explorer'          =>  'Tax Table',
                ],
                'Customers' => [
                    'index_gst_type_explorer'           =>  'GST Type',
                    'index_tcs_status_explorer'         =>  'TCS Status',
                    'index_tds_status_explorer'         =>  'TDS Status',
                    'index_payment_mode_explorer'       =>  'Payment Mode',
                    'index_incentive_mode_explorer'     =>  'Incentive Mode',
                ],
                'Price Master' => [
                    'index_price_list_explorer'         =>  'Price List',
                    'index_customer_price_explorer'     =>  'Customers',
                    'index_product_price_explorer'      =>  'Products',
                ],
                'Stocks' => [
                    'show_current_stock'                =>  'Current Stock',
                    'show_stock_register'               =>  'Stock Register',
                ],
                'Data Explorer' => [
                    'show_bank_payment'                 =>  'Bank Payments',
                    'show_cash_register'                =>  'Cash Register',
                ],
            ],

            'Reports' => [
                'Sales Reports' => [
                    'show_item_wise_report'             =>  'Item wise Report',
                    'show_hsn_wise_report'              =>  'HSN wise Report',
                    'show_tax_wise_report'              =>  'Tax wise Report',
                    'show_b2b_b2c_report'               =>  'B2B / B2C Report',
                    'show_zero_value_items_report'      =>  'Zero Value Items Report',
                    'show_item_wise_customer_report'    =>  'Item wise Customer Report',
                    'show_customer_wise_item_report'    =>  'Customer wise Item Report',
                ],
                'Customer Reports' => [
                    'show_customer_statement_report'    =>  'Customer Statement Report',
                    'show_customer_account_report'      =>  'Customer Account Report',
                ],
                'Reports' => [
                    'show_invoice_report'               =>  'Invoice Report',
                    'show_day_wise_report'              =>  'Day wise Report',
                    'show_bill_wise_report'             =>  'Bill wise Report',
                    'show_transaction_report'           =>  'Transaction Report',
                    'show_document_summary_report'      =>  'Document Summary Report',
                ],
            ],
        ];

        return $permissions;
    }
}