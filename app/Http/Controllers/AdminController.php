<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Profiles\Customer;
use App\Models\Profiles\Employee;
use App\Models\Places\MRoute;
use App\Models\Places\Area;
use App\Models\Products\Product;
use App\Models\Transactions\Enquiry;
use App\Models\Transactions\MobileData;
use App\Models\Masters\CashDenomination;
use App\Models\Transactions\BatchDenomination;
use App\Models\Transactions\Receipt;
use App\Models\Transactions\ReceiptData;
use App\Models\Transactions\ExpenseDenomination;
use App\Models\Transactions\OpeningBalance;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Masters\Pricing\PriceMaster;
use Carbon\Carbon;

class AdminController extends Controller
{        
    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function index()
    {        
        //return redirect('/admin/admin-index');
        // return view('/admin/admin-index');

        $products   = Product::count();
        $customers  = Customer::count();
        $employees  = Employee::count();
        $routes     = MRoute::count();
        $areas      = Area::count();
        $enquiries  = Enquiry::count();        

        return view('admin.admin-index', [
            'products'  => $products,
            'customers' => $customers,
            'employees' => $employees,
            'routes'    => $routes,
            'areas'     => $areas,
            'enquiries' => $enquiries
        ]); 
        
    }

    public function backupDatabase()
    {        
        $mysqlHostName  = env('DB_HOST');
        $mysqlUserName  = env('DB_USERNAME');
        $mysqlPassword  = env('DB_PASSWORD');
        $DbName         = env('DB_DATABASE');
        $file_name = 'database_backup_on_' . date('y-m-d') . '.sql';

        // $tables  = array("users","products","categories"); //here your tables...
        $queryTables = \DB::select(\DB::raw('SHOW TABLES'));
        foreach ( $queryTables as $table )
        {
            foreach ( $table as $tName)
            {
                $tables[]= $tName ;
            }
        }

        $connect = new \PDO("mysql:host=$mysqlHostName;dbname=$DbName;charset=utf8", "$mysqlUserName", "$mysqlPassword",array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        $get_all_table_query = "SHOW TABLES";
        $statement = $connect->prepare($get_all_table_query);
        $statement->execute();
        $result = $statement->fetchAll();
        $output = '';
        foreach($tables as $table)
        {
            $show_table_query = "SHOW CREATE TABLE " . $table . "";
            $statement = $connect->prepare($show_table_query);
            $statement->execute();
            $show_table_result = $statement->fetchAll();

            foreach($show_table_result as $show_table_row)
            {
                if(array_key_exists("Create Table",$show_table_row)) {
                    $output .= "\n\n" . $show_table_row["Create Table"] . ";\n\n";
                }
                else {
                    $output .= "\n\n" . $show_table_row["Create View"] . ";\n\n";
                }
                //$output .= "\n\n" . implode(" " , array_keys($show_table_row)) . ";\n\n";
            }            
            $select_query = "SELECT * FROM " . $table . "";
            $statement = $connect->prepare($select_query);
            $statement->execute();
            $total_row = $statement->rowCount();

            for($count=0; $count<$total_row; $count++)
            {
                $single_result = $statement->fetch(\PDO::FETCH_ASSOC);
                $table_column_array = array_keys($single_result);
                $table_value_array = array_values($single_result);
                $output .= "\nINSERT INTO $table (";
                $output .= "" . implode(", ", $table_column_array) . ") VALUES (";
                $output .= "'" . implode("','", $table_value_array) . "');\n";
            }
        }

        // return $output;
        $file_handle = fopen($file_name, 'w+');
        fwrite($file_handle, $output);
        fclose($file_handle);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_name));
        ob_clean();
        flush();
        readfile($file_name);
        unlink($file_name);
    }
    
    public function mobileSecurity()
    {
        $mobileData = MobileData::select('id','user_id','mobile_num','app_version','model','android_version','unique_code','created_at')
                                ->with('user:id,name,code')
                                ->orderByDesc('id')
                                ->get();
        // return response()->json([
        return view('masters.mobile_security', [
            'mobileData' => $mobileData
        ]);
    }
    
    public function destroyMobileData($id)
    {                   
        $mobileData = MobileData::find($id);
        $mobileData->delete();
        return response()->json([ 'success' => true ]);
    }    

  	public function clearRoute()
    {
        \Artisan::call('route:clear');
        echo 'Routes Cleared Successfully';
    }

    public function logout()
    {
        Auth::logout(); 
        return redirect()->route('login');
    }    

    public function updateJsonColumn()
    {
        // $this->flattenJsonArrayColumn(CashDenomination::class, 'denomination');
        // $this->flattenJsonArrayColumn(BatchDenomination::class, 'denomination');
        $this->flattenJsonArrayColumn(Receipt::class, 'denomination');
        // $this->flattenJsonArrayColumn(ExpenseDenomination::class, 'denomination');
        // $this->flattenJsonArrayColumn(OpeningBalance::class, 'denomination');
    }

    private function flattenJsonArrayColumn(string $modelClass, string $jsonField)
    {
        $model = new $modelClass;
    
        // Fetch all records with non-null and non-empty JSON field
        $records = $model->whereNotNull($jsonField)->get();
    
        foreach ($records as $record) {
            $jsonValue = $record->{$jsonField};
    
            // Decode as associative array
            $array = json_decode($jsonValue, true);
    
            // If it's not an array
            if (!is_array($array)) continue;
    
            // Detect if already flattened, then return
            $first = reset($array);
            $isAlreadyFlat = !is_array($first);
            if ($isAlreadyFlat) continue;

            $flattened = [];
    
            foreach ($array as $item) {
                if (is_array($item)) {
                    foreach ($item as $key => $value) {
                        $flattened[$key] = $value;
                    }
                }
            }
    
            // Update only if the flattening is different
            $newJson = json_encode($flattened);
            if ($newJson !== $jsonValue) {
                $record->{$jsonField} = $newJson;
                $record->save();
            }
        }

        echo 'Updated Successfully!';
    }

    public function migrateIncentives() {
        $oldRecords = DB::table('incentives_datas')
            ->where('status', '!=', 'Cancelled')
            ->orderBy('id')
            ->get();

        $counter = 1;

        foreach ($oldRecords as $record) {
            $incentiveNumber = 'INC-' . str_pad($counter++, 3, '0', STR_PAD_LEFT);

            // Decode JSON fields
            $items  = json_decode($record->incentive_data, true);
            $totals = json_decode($record->incentive_total, true);

            $incentiveTotal = array_sum(array_column($items, 'inc_amount'));
            $leakageTotal   = array_sum(array_column($items, 'lk_amount'));
            $tdsAmount      = $totals['tdsAmount'] ?? 0;
            $totalAmount    = $incentiveTotal + $leakageTotal - $tdsAmount;
            $netAmount      = round($totalAmount);
            $roundOff       = $netAmount - $totalAmount;

            // Insert into `incentives`
            DB::table('incentives')->insert([
                'incentive_number' => $incentiveNumber,
                'incentive_date'   => Carbon::parse($record->created_at)->format('Y-m-d'),
                'customer_id'      => (int) $record->customer_id,
                'customer_name'    => $record->customer_name,
                'from_date'        => $record->from_date,
                'to_date'          => $record->to_date,
                'incentive_total'  => $incentiveTotal,
                'leakage_total'    => $leakageTotal,
                'tds_amount'       => $tdsAmount,
                'round_off'        => $roundOff,
                'net_amount'       => $netAmount,
                'incentive_status' => ($record->status === 'Accepted') ? 'Approved' : 'Pending',
                'payment_status'   => $record->payment_status,
                'created_at'       => $record->created_at,
                'updated_at'       => $record->updated_at,
            ]);

            // Insert into `incentive_items`
            foreach ($items as $item) {
                DB::table('incentive_items')->insert([
                    'incentive_number' => $incentiveNumber,
                    'item_id'          => (int) $item['id'],
                    'item_name'        => $item['product'],
                    'qty'              => $item['qty'],
                    'inc_rate'         => $item['inc_rate'],
                    'inc_amt'          => $item['inc_amount'],
                    'lkg_qty'          => $item['lk_qty'],
                    'lkg_amt'          => $item['lk_amount'],
                    'created_at'       => $record->created_at,
                    'updated_at'       => $record->updated_at,
                ]);
            }

            DB::table('incentives_datas')
                ->where('id', $record->id)
                ->update(['incentive_num' => $incentiveNumber]);

            DB::table('incentive_payments')
                ->where('incentive_id', $record->id)
                ->update(['incentive_num' => $incentiveNumber]);            
        }

        $records = DB::table('incentive_payments')            
            ->orderBy('id')
            ->get();

        foreach($records as $record) {
            $customerId = DB::table('incentives')->where('incentive_number',$record->incentive_num)->value('customer_id');
            DB::table('incentive_payouts')->insert([
                'incentive_number' => $record->incentive_num,
                'customer_id'      => $customerId, 
                'document_date'    => $record->payment_date,
                'amount'           => $record->amount,
                'payout_mode'      => 'Receipt',
                'reference_number' => $record->reference_num,                
                'payout_status'    => 'Approved',                
                'approval_date'    => $record->payment_date,
            ]);
        }

        echo "Incentives Migrated Successfully";
    }

    public function executeQuery()
    {
        try {
            DB::transaction(function () {
                DB::update("
                    UPDATE incentive_payouts ip
                    JOIN incentives i ON ip.incentive_number = i.incentive_number
                    SET ip.customer_id = i.customer_id
                ");
            });

            return response()->json(['message' => 'Customer IDs updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function clearCache(): void
    {
        \Artisan::call('optimize:clear');
        \Artisan::call('permission:cache-reset');

        echo 'All caches cleared successfully.';
    }
    
    public function gstUpdate()
    {
        try {
            DB::transaction(function () {
                DB::update("UPDATE products SET gst=5, sgst=2.5, cgst=2.5, igst=5 WHERE gst=12");
                DB::update("UPDATE gst_master SET gst=5, sgst=2.5, cgst=2.5, igst=5 WHERE gst=12");        
            });

            return response()->json(['message' => 'GST updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function modifyReceipts(Request $request)
    {
        $receiptNumber = $request->receipt_num;
        $invoiceNumber = $request->invoice_num;
        $newOustdAmt = $request->amount;

        DB::transaction(function () use ($receiptNumber, $invoiceNumber, $newOustdAmt) {

            // Step 1: Get receipt
            $receipt = Receipt::where('receipt_num', $receiptNumber)->firstOrFail();
            $receiptId = $receipt->id;
            $receiptAmount = $receipt->amount;

            // Step 2: Get current JSON list
            $items = collect(json_decode($receipt->receipt_data, true));

            // Step 3: Filter items starting from the target invoice
            $startIndex = $items->search(fn ($row) => $row['inv_num'] === $invoiceNumber);

            if ($startIndex === false) {
                throw new \Exception("Invoice number {$invoiceNumber} not found in receipt JSON.");
            }

            // Keep only items from target invoice onward
            $updated = $items->slice($startIndex)->values()->toArray();
            
            // Step 4: Update the receipt data with distributed amounts
            $updated[0]['oustd_amt'] = $newOustdAmt;
            $remaining = $receiptAmount;

            for ($i = 0; $i < count($updated); $i++) {

                // If no remaining amount left, set rcvd_amt = null
                if ($remaining <= 0) {
                    $updated[$i]['rcvd_amt'] = null;
                    continue;
                }

                $oustd = $updated[$i]['oustd_amt'];

                // Case 1: remaining >= outstanding → full payment
                if ($remaining >= $oustd) {
                    $updated[$i]['rcvd_amt'] = $oustd;
                    $remaining -= $oustd;
                }

                // Case 2: remaining < outstanding → partial payment
                else {
                    $updated[$i]['rcvd_amt'] = $remaining;
                    $remaining = 0; // stop distribution
                }
            }

            // Step 5: Recreate JSON and update in receipts table
            $receipt->update([
                'receipt_data' => json_encode($updated)
            ]);

            // Step 6: Update receipt_data table
            ReceiptData::where('receipt_id', $receiptId)->delete();

            foreach ($updated as $row) {
                if($row['rcvd_amt']) {
                    $inv = $row['inv_num'];
                    $amt = $row['rcvd_amt'];
                    $os  = $row['oustd_amt'];

                    $status = ($os == $amt) ? 'Paid' : 'Outstanding';

                    ReceiptData::create([
                        'receipt_id'     => $receiptId,
                        'receipt_date'   => $receipt->receipt_date,
                        'invoice_number' => $inv,
                        'amount'         => $amt,
                        'receipt_status' => $status,
                    ]);

                    // Step 7: Update invoice master table
                    $invoiceModel = str_contains($inv, 'P')
                        ? SalesInvoice::class
                        : TaxInvoice::class;

                    $invoiceModel::where('invoice_num', $inv)
                        ->update(['receipt_status' => $status]);
                }
            }
        });

        echo "Updated Successfully!";
    }

    public function formatPriceMasterJsonData()
    {
        PriceMaster::chunk(100, function ($masters) {
            foreach ($masters as $master) {

                // Convert customer_ids ["82","122"] → [82,122]
                $master->customer_ids = array_map('intval', $master->customer_ids ?? []);

                // Convert price_list values {"8":"67"} → {"8":67}
                $master->price_list = collect($master->price_list ?? [])
                    ->map(function ($value) {
                        return is_numeric($value) ? $value + 0 : $value;
                    })
                    ->toArray();

                $master->save();
            }
        });

        echo "Formatted Successfully!";
    }

    public function listPermissions()
    {
        echo auth()->user()->getAllPermissions()->pluck('name');
    }
}
