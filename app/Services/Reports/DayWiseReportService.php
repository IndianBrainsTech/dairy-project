<?php

namespace App\Services\Reports;

use App\Repositories\Reports\DayWiseReportRepository;
use Carbon\Carbon;

class DayWiseReportService
{
    public function __construct(
        protected DayWiseReportRepository $repo
    ) {}

    public function getReport(array $params): array
    {
        $fromDate   = $params['fromDate'];
        $toDate     = $params['toDate'];
        $routeId    = $params['routeId'];
        $reportType = $params['reportType'];

        $banks  = $this->repo->getBanks();
        $routes = $this->resolveRoutes($routeId);

        $final = [];

        foreach ($routes as $route) {

            $customers = $this->repo->getCustomersByRoute($route['id']);
            if ($customers->isEmpty()) continue;

            $ids = $customers->pluck('id')->toArray();

            // Bulk Data
            $invoiceMap  = $this->repo->getInvoiceAmounts($ids, $fromDate, $toDate);
            $receiptMap  = $this->repo->getReceiptAmounts($ids, $fromDate, $toDate);
            $returnMap   = $this->repo->getReturnAmounts($ids, $fromDate, $toDate);
            $discountMap = $this->repo->getDiscounts($ids, $fromDate, $toDate);

            $prevMap     = $this->calculatePreviousInvoice($ids, $fromDate);
            $openingMap  = $this->calculateOpeningBalance($ids, $fromDate);

            $data = $this->buildRecords(
                $customers,
                $banks,
                $invoiceMap,
                $receiptMap,
                $returnMap,
                $discountMap,
                $prevMap,
                $openingMap,
                $reportType
            );

            // Skip route if no records (optional but recommended)
            if (empty($data['grouped'])) {
                continue;
            }

            $final[] = [
                'route'        => $route['name'],
                'routeRecords' => $data['grouped'],
                'routeTotals'  => $data['routeTotals'],
            ];
        }

        // Calculate totals
        $grandTotals    = $this->calculateTotals($final);
        $payModeRecords = $this->calculatePayModeTotals($final);

        // Apply formatting layer
        $formatted = $this->formatReportOutput(
            $final,
            $grandTotals,
            $payModeRecords
        );

        return [
            'reportData'     => $formatted['reportData'],
            'grandTotals'    => $formatted['grandTotals'],
            'payModeRecords' => $formatted['payModeRecords'],
            'banks'          => $banks
        ];
    }

    /* -------------------------------------------------- */
    /* BUSINESS LOGIC */
    /* -------------------------------------------------- */

    private function calculatePreviousInvoice($ids, $date)
    {
        $prevDate = getPreviousDate($date);

        $inv  = $this->repo->getInvoiceAmounts($ids,$prevDate,$prevDate);
        $rcpt = $this->repo->getReceiptAmounts($ids,$prevDate,$prevDate);
        $ret  = $this->repo->getReturnAmounts($ids,$prevDate,$prevDate);
        $disc = $this->repo->getDiscounts($ids, $prevDate, $prevDate);

        $map = [];

        foreach ($ids as $cid) {
            $invoice = $inv[$cid] ?? 0;

            if ($invoice == 0) {
                $map[$cid] = 0;
                continue;
            }

            $received =
                ($rcpt[$cid]['Cash'][0] ?? 0) +
                array_sum($rcpt[$cid]['Bank'] ?? []) +
                ($rcpt[$cid]['Incentive'][0] ?? 0) +
                ($rcpt[$cid]['Deposit'][0] ?? 0) +
                ($ret[$cid] ?? 0) + 
                ($disc[$cid] ?? 0);

            $map[$cid] = $invoice - $received;
        }

        return $map;
    }

    private function calculateOpeningBalance($ids, $date)
    {
        $prevDate = Carbon::parse($date)->subDay()->endOfDay();

        $outstanding = $this->repo->getOutstanding($ids);
        $inv         = $this->repo->getInvoiceAmountsUpTo($ids,$prevDate);
        $rcpt        = $this->repo->getReceiptAmountsUpTo($ids,$prevDate);
        $ret         = $this->repo->getReturnAmountsUpTo($ids,$prevDate);
        $disc        = $this->repo->getDiscountsUpTo($ids, $prevDate);

        $map = [];

        foreach ($ids as $cid) {
            $map[$cid] =
                ($outstanding[$cid] ?? 0)
                + ($inv[$cid] ?? 0)
                - ($rcpt[$cid] ?? 0)
                - ($ret[$cid] ?? 0)
                - ($disc[$cid] ?? 0);
        }

        return $map;
    }

    private function buildRecords($customers, $banks, $invoiceMap, $receiptMap, $returnMap, $discountMap, $prevMap, $openingMap, $type)
    {
        $grouped = [];
        $routeTotals = [];

        foreach ($customers as $c) {

            $cid = $c->id;

            $record = [
                'customer' => $c->customer_name,
                'open_bal' => $openingMap[$cid] ?? 0,
                'inv_amt'  => $invoiceMap[$cid] ?? 0,
                'prev_inv' => $prevMap[$cid] ?? 0,
                'cash'     => $receiptMap[$cid]['Cash'][0] ?? 0,
                'bank'     => array_sum($receiptMap[$cid]['Bank'] ?? []),
                'incentive'=> $receiptMap[$cid]['Incentive'][0] ?? 0,
                'deposit'  => $receiptMap[$cid]['Deposit'][0] ?? 0,
                'others'   => $returnMap[$cid] ?? 0,
                'discount' => $discountMap[$cid] ?? 0,
            ];

            $record['day_bal'] =
                $record['inv_amt']
                - $record['cash']
                - $record['bank']
                - $record['incentive']
                - $record['deposit']
                - $record['others']
                - $record['discount']; 

            $record['close_bal'] = $record['open_bal'] + $record['day_bal'];

            $record['yest_bal'] =
                ($record['prev_inv']
                    ? $record['prev_inv']
                        - $record['cash']
                        - $record['bank']
                        - $record['incentive']
                        - $record['deposit']
                        - $record['others']
                        - $record['discount']
                    : 0);

            // Format2 bank-wise columns
            if ($type === 'Format2') {
                foreach ($banks as $bank) {
                    $record[$bank->display_name] =
                        $receiptMap[$cid]['Bank'][$bank->id] ?? 0;
                }
            }
            
            // SKIP ZERO RECORDS             
            $numericValues = collect($record)
                ->except(['customer'])
                ->filter(fn($v) => is_numeric($v));

            if ($numericValues->sum() == 0) {
                continue; // Skip this customer completely
            }

            $mode = $c->payment_mode;

            // Initialize structure if not exists
            if (!isset($grouped[$mode])) {
                $grouped[$mode] = [
                    'records' => [],
                    'totals'  => []
                ];
            }

            // Push record
            $grouped[$mode]['records'][] = $record;

            // Update totals
            foreach ($numericValues as $k => $v) {

                $grouped[$mode]['totals'][$k] =
                    ($grouped[$mode]['totals'][$k] ?? 0) + $v;

                $routeTotals[$k] =
                    ($routeTotals[$k] ?? 0) + $v;
            }
        }

        return compact('grouped','routeTotals');
    }
    
    private function calculateTotals($data)
    {
        $totals = [];

        foreach ($data as $route) {
            foreach ($route['routeTotals'] as $k => $v) {
                $totals[$k] = ($totals[$k] ?? 0) + $v;
            }
        }

        return $totals;
    }

    private function calculatePayModeTotals($data)
    {
        $totals = [];

        foreach ($data as $route) {
            foreach ($route['routeRecords'] as $mode => $rec) {
                foreach ($rec['totals'] as $k => $v) {
                    $totals[$mode][$k] = ($totals[$mode][$k] ?? 0) + $v;
                }
            }
        }

        return collect($totals)
            ->map(fn($d,$m)=>array_merge(['pay_mode'=>$m],$d))
            ->values()
            ->toArray();
    }

    private function resolveRoutes($routeId)
    {
        $special = [
            -1 => 'Company',
            -2 => 'Function',
        ];

        // If a specific routeId is requested
        if ($routeId) {
            if (isset($special[$routeId])) {
                return [['id' => $routeId, 'name' => $special[$routeId]]];
            }

            $route = $this->repo->getRoutes()->firstWhere('id', $routeId);
            return $route ? [['id' => $routeId, 'name' => $route->name]] : [];
        }

        // Otherwise return all routes + specials
        $routes = $this->repo->getRoutes()->map(function ($r) {
            return ['id' => $r->id, 'name' => strtoupper($r->name)];
        })->toArray();

        return array_merge(
            [
                ['id' => -1, 'name' => 'Company'],
                ['id' => -2, 'name' => 'Function'],
            ],
            $routes
        );
    }

    private function formatReportOutput(array $reportData, array $grandTotals, array $payModeRecords)
    {
        /**
         * 1️⃣ Format RECORDS (0 → "", .00 → (int))
         */
        foreach ($reportData as &$route) {

            foreach ($route['routeRecords'] as &$group) {

                // Format each customer record
                foreach ($group['records'] as &$record) {
                    foreach ($record as $key => $value) {
                        if ($key === 'customer') 
                            continue;

                        if (is_numeric($value)) {
                            // If value is exactly 0, replace with empty string
                            if ($value == 0) {
                                $record[$key] = "";
                            }                             
                            // If value has no fractional part (e.g. 25.00, 25.0000), cast to int
                            elseif (fmod((float)$value, 1) == 0.0) {
                                $record[$key] = (int)$value;
                            }
                        }
                    }
                }

                /**
                 * 2️⃣ Format GROUP TOTALS
                 */
                foreach ($group['totals'] as $key => $value) {
                    $group['totals'][$key] = formatIndianNumber($value);
                }
            }

            /**
             * 3️⃣ Format ROUTE TOTALS
             */
            foreach ($route['routeTotals'] as $key => $value) {
                $route['routeTotals'][$key] = formatIndianNumber($value);
            }
        }

        /**
         * 4️⃣ Format GRAND TOTALS
         */
        foreach ($grandTotals as $key => $value) {
            $grandTotals[$key] = formatIndianNumber($value);
        }

        /**
         * 5️⃣ Format PAY MODE TOTALS
         */
        foreach ($payModeRecords as &$record) {
            foreach ($record as $key => $value) {
                if ($key === 'pay_mode') continue;

                $record[$key] = formatIndianNumber($value);
            }
        }

        return [
            'reportData'     => $reportData,
            'grandTotals'    => $grandTotals,
            'payModeRecords' => $payModeRecords,
        ];
    }
}