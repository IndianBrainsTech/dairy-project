<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Masters\BankMaster;
use App\Models\Transactions\BankPayment;
use App\Models\Transactions\IncentivePayout;
use App\Models\Transport\DieselBillPayment;
use App\Services\ExcelTemplateService;
use App\Enums\PaymentType;

class ExcelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function downloadBankPayments(Request $request, ExcelTemplateService $excel)
    {
        $bankPayment = BankPayment::findOrFail($request->id);

        $paymentType = $bankPayment->payment_type;
        $bank        = $bankPayment->bank_name;
        $date        = $bankPayment->payment_date;
        $numbers     = $bankPayment->reference_numbers;
        $dateExcel   = $bankPayment->getPaymentDateForExcel();

        $records  = [];
        $fileName = '';

        if ($paymentType === PaymentType::INCENTIVE) {
            if ($bank === 'KVB') {
                $fileName = "Incentive_Payments_{$date}_KVB.xlsx";
                $records  = $this->generateIncentiveRecordsKVB($numbers);
            } 
            elseif ($bank === 'HDFC') {
                $fileName = "Incentive_Payments_{$date}_HDFC.xlsx";
                $records  = $this->generateIncentiveRecordsHDFC($numbers, $dateExcel);
            }
        } 
        elseif ($paymentType === PaymentType::DIESEL_BILL) {
            if ($bank === 'KVB') {
                $fileName = "Diesel_Bill_Payments_{$date}_KVB.xlsx";
                $records  = $this->generateDieselBillRecordsKVB($numbers);
            } 
            elseif ($bank === 'HDFC') {
                $fileName = "Diesel_Bill_Payments_{$date}_HDFC.xlsx";
                $records  = $this->generateDieselBillRecordsHDFC($numbers, $dateExcel);
            }
        }

        // return response()->json([
        //     'file_name' => $fileName,
        //     'records'   => $records,
        // ]);

        $file = $excel->generate($records, $fileName, $bank, $date);

        return $excel->download($file);
    }

    private function generateIncentiveRecordsKVB(array $paymentIds): array
    {
        $payments = IncentivePayout::select('id', 'customer_id', 'amount')
            ->with(['customer:id,customer_name,ifsc,acc_holder,acc_number'])
            ->whereIn('id', $paymentIds)
            ->get();

        return $this->buildKvbTransferRecords($payments, function ($payment) {
            $customer = $payment->customer;

            return [
                $customer->customer_name,   // name
                $payment->amount,           // amount
                $customer->acc_holder,      // accountHolder
                $customer->acc_number,      // accountNumber
                $customer->ifsc,            // ifsc
                'Incentive',                // paymentFor
            ];
        });
    }

    private function generateDieselBillRecordsKVB(array $paymentIds): array
    {
        $payments = DieselBillPayment::select('id', 'statement_id', 'amount')
            ->with([
                'statement:id,bunk_id',
                'statement.bunk:id,name,bank_id,branch_id,account_holder,account_number',
                'statement.bunk.branch:id,ifsc'
            ])
            ->whereIn('id', $paymentIds)
            ->get();

        return $this->buildKvbTransferRecords($payments, function ($payment) {
            $bunk = $payment->statement->bunk;

            return [
                $bunk->name,            // name
                $payment->amount,       // amount
                $bunk->account_holder,  // accountHolder
                $bunk->account_number,  // accountNumber
                $bunk->branch->ifsc,    // ifsc
                'Diesel Bill',          // paymentFor
            ];
        });
    }

    private function generateIncentiveRecordsHDFC(array $paymentIds, string $date): array
    {
        $payments = IncentivePayout::select('id', 'customer_id', 'incentive_number', 'amount')
            ->with([
                'customer:id,customer_name,customer_code,email_id,bank_name,branch,ifsc,acc_holder,acc_number',
                'incentiveNumber:id,incentive_number,from_date,to_date'
            ])
            ->whereIn('id', $paymentIds)
            ->get();

        return $this->buildHdfcTransferRecords($payments, function ($payment) use ($date) {
            $customer = $payment->customer;
            $period = formatDateRangeAsDMY($payment->incentiveNumber->from_date, $payment->incentiveNumber->to_date);

            return [
                $customer->customer_name,   // name
                $date,                      // date
                $period,                    // period
                'Incentive',                // paymentFor
                $customer->customer_code,   // customerReference
                $customer->email_id,        // emailId
                $payment->amount,           // amount
                $customer->bank_name,       // bankName
                $customer->branch,          // branchName
                $customer->acc_holder,      // accountHolder
                $customer->acc_number,      // accountNumber
                $customer->ifsc,            // ifsc
            ];
        });
    }

    private function generateDieselBillRecordsHDFC(array $paymentIds, string $date): array
    {
        $payments = DieselBillPayment::select('id', 'statement_id', 'amount')
            ->with([
                'statement:id,bunk_id,from_date,to_date',
                'statement.bunk:id,name,bank_id,branch_id,account_holder,account_number',
                'statement.bunk.bank:id,name',
                'statement.bunk.branch:id,name,ifsc'
            ])
            ->whereIn('id', $paymentIds)
            ->get();

        return $this->buildHdfcTransferRecords($payments, function ($payment) use ($date) {
            $bunk = $payment->statement->bunk;

            return [
                $bunk->name,                 // name
                $date,                       // date
                $payment->statement->period, // period
                'Diesel Bill',               // paymentFor
                '',                          // customerReference
                null,                        // emailId
                $payment->amount,            // amount
                $bunk->bank->name,           // bankName
                $bunk->branch->name,         // branchName
                $bunk->account_holder,       // accountHolder
                $bunk->account_number,       // accountNumber
                $bunk->branch->ifsc,         // ifsc
            ];
        });
    }

    private function buildKvbTransferRecords(iterable $items, callable $resolver): array
    {
        $kvbAccountNumber = BankMaster::where('id', 1)->value('acc_number');
        $records = [];
        $i = 1;

        foreach ($items as $item) {
            [$name, $amount, $accountHolder, $accountNumber, $ifsc, $paymentFor] = $resolver($item);

            $transactionType = str_starts_with($ifsc, 'KVBL')
                ? 'INTERNAL TRANSFER'
                : 'IMPS TRANSFER-IFSC';

            $records[] = [
                'transaction_type'                => $transactionType,
                'debting_account_number'          => $kvbAccountNumber,
                'beneficiary_ifsc_code'           => $ifsc,
                'beneficiary_account_number'      => $accountNumber,
                'beneficiary_name'                => $accountHolder,
                'beneficiary_address_line_1'      => '',
                'beneficiary_address_line_2'      => '',
                'beneficiary_address_line_3'      => '',
                'beneficiary_address_line_4'      => '',
                'transaction_reference_number'    => $i++,
                'amount'                          => $amount,
                'sender_to_receiver_info'         => $name,
                'additional_info_1_account_type'  => '',
                'additional_info_2_mobile_number' => '',
                'additional_info_3_mmid'          => '',
                'additional_info_4'               => $paymentFor,
            ];
        }

        return $records;
    }

    private function buildHdfcTransferRecords(iterable $items, callable $resolver): array
    {
        $defaultMail = 'assaineft@gmail.com';
        $records = [];

        foreach ($items as $item) {
            [$name, $date, $period, $paymentFor, $customerReference, $emailId, $amount, 
                $bankName, $branchName, $accountHolder, $accountNumber, $ifsc] = $resolver($item);

            $transactionType = str_starts_with($ifsc, 'HDFC') ? "I" : "N";

            $records[] = [
                'transaction_type'               => $transactionType,
                'beneficiary_code'               => '',
                'beneficiary_account_number'     => $accountNumber,
                'amount'                         => $amount,
                'beneficiary_name'               => $accountHolder,
                'to_be_left_blank1'              => '',
                'to_be_left_blank2'              => '',
                'to_be_left_blank3'              => '',
                'to_be_left_blank4'              => '',
                'to_be_left_blank5'              => '',
                'to_be_left_blank6'              => '',
                'to_be_left_blank7'              => '',
                'to_be_left_blank8'              => '',
                'customer_reference_number'      => $customerReference,
                'payment_detail_1'               => $name,
                'payment_detail_2'               => $paymentFor,
                'payment_detail_3'               => $period,
                'payment_detail_4'               => '',
                'payment_detail_5'               => '',
                'payment_detail_6'               => '',
                'payment_detail_7'               => '',
                'to_be_left_blank9'              => '',
                'inst_date'                      => $date,
                'to_be_left_blank10'             => '',
                'ifsc_code'                      => $ifsc,
                'beneficiary_bank_name'          => $bankName,
                'beneficiary_bank_branch_name'   => $branchName,
                'beneficiary_email_id'           => $emailId ?? $defaultMail,
            ];
        }

        return $records;
    }
}