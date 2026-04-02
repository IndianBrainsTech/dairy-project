<?php

namespace App\Http\Traits;

use App\Models\Orders\Order;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Orders\JobWork;
use App\Models\Masters\Setting;
use App\Models\Masters\Pricing\PriceMaster;
use App\Models\Profiles\Customer;
use App\Models\Places\Address;
use App\Models\Products\ProductUnit;
use App\Models\Products\UOM;

trait SalesUtility
{
    protected function getReferenceNumber($key)
    {
        $dataPart = Setting::where('category', 'Invoice')->where('key', $key)->value('value');
        $referenceNumber = "";
        if($key == "order")
            $referenceNumber = Order::latest('id')->value('order_num');
        else if($key == "sales-invoice")
            $referenceNumber = SalesInvoice::latest('id')->value('invoice_num');
        else if($key == "tax-invoice")
            $referenceNumber = TaxInvoice::latest('id')->value('invoice_num');
        else if($key == "bulk-milk")
            $referenceNumber = BulkMilkOrder::latest('id')->value('invoice_num');
        else if($key == "conversion")
            $referenceNumber = JobWork::latest('id')->value('job_work_num');

        if ($referenceNumber) {
            $numberPart = substr($referenceNumber, strlen($dataPart));
            $nextNumber = sprintf('%06d', intval($numberPart) + 1);
            $referenceNumber = $dataPart . $nextNumber;
        }
        else {
            $referenceNumber = $dataPart . '000001';
        }
        
        return $referenceNumber;
    }

    protected function convertToPrimary($productId, $qty, $unitId)
    {        
        $productUnits = ProductUnit::where('product_id',$productId)
                                    ->orderByDesc('prim_unit')
                                    ->get(['unit_id','prim_unit','conversion']);

        // Primary Unit
        $primaryUnit = $productUnits->first();
        $primUnit = UOM::where('id', $primaryUnit->unit_id)->value('display_name');
        $primQty = $qty;

        // Conversion
        if ($unitId != $primaryUnit->unit_id) {
            foreach ($productUnits->skip(1) as $productUnit) {
                if ($unitId == $productUnit->unit_id) {
                    $primQty = $qty * $productUnit->conversion;
                    break;
                }
            }
        }
        
        return [
            'prim_qty' => getTwoDigitPrecision($primQty),
            'prim_unit' => $primUnit
        ];
    }

    protected function getCustomerBillingData($customerId, $billAddrId, $deliAddrId)
    {
        $customer = Customer::select('address_lines','district','state','pincode','contact_num','gst_number')->where('id',$customerId)->first();
        $data['mobile']   = $customer->contact_num;
        $data['gst']      = $customer->gst_number;
        $data['billId']   = $billAddrId;
        $data['billAddr'] = $this->getFormattedAddress($billAddrId, $customer);
        $data['deliId']   = $deliAddrId;
        $data['deliAddr'] = $this->getFormattedAddress($deliAddrId, $customer);        
        return $data;
    }

    private function getFormattedAddress($addressId, $addressData)
    {
        if ($addressId <> 0) {
            $addressData = Address::select('address_lines', 'district', 'state', 'pincode')
                ->where('id', $addressId)
                ->first();
        }
    
        $address = implode(",\r\n", [
            $addressData->address_lines,
            $addressData->district,
            $addressData->state,
        ]);
    
        if ($addressData->pincode) {
            $address .= " - " . $addressData->pincode;
        }
    
        return $address;
    }

    protected function getPriceList($customerId)
    {
        $priceList = ProductUnit::where('prim_unit', 1)
                                ->orderBy('product_id')
                                ->pluck('price', 'product_id'); // Fetch as key-value pairs (product_id => price)

        $masterPriceList = $this->getMasterPriceList($customerId);

        if ($masterPriceList) {
            // Merge master prices into the standard price list
            foreach ($masterPriceList as $productId => $price) {
                $priceList[$productId] = floatval($price);
            }
        }

        return $priceList;
    }

    protected function getPriceListWithUnits($customerId)
    {
        $standardPriceList = $this->getStandardPriceList();
        $masterPriceList = $this->getMasterPriceList($customerId);

        $priceList = $standardPriceList->toArray();
        if($masterPriceList) {
            // Update prices from the master price list
            foreach ($priceList as &$item) {
                $item['price'] = floatval($masterPriceList[$item['product_id']] ?? $item['price']);
            }
        }

        return $priceList;
    }

    private function getStandardPriceList()
    {
        $priceList = ProductUnit::where('prim_unit', 1)
                                ->orderBy('product_id')
                                ->get(['product_id', 'unit_id', 'price']);

        $units = UOM::pluck('display_name','id');

        foreach($priceList as $record) {
            $record->unit        = $units[$record->unit_id];
            $record->other_units = ProductUnit::where('product_id', $record->product_id)
                                        ->where('prim_unit', 0)
                                        ->get(['unit_id', 'conversion']);
        }

        return $priceList;
    }

    private function getMasterPriceList(int $customerId): array
    {
        $priceList = PriceMaster::where('status', 'ACTIVE')
            ->whereDate('effect_date', '<=', now()->toDateString())
            ->whereJsonContains('customer_ids', $customerId)
            ->orderByDesc('id')
            ->value('price_list');

        return $priceList ?? [];
    }
}
