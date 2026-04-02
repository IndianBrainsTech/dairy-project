<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Masters\Pricing\PriceMaster;
use App\Models\Profiles\Customer;
use App\Models\Places\MRoute;
use App\Enums\PriceMasterStatus;

class TaskController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }
/*
    public function createPriceMasters(Request $request)
    {
        $routeId = 2;
        $group = "Retailer";
        $masterId = 4; // 48

        $master = PriceMaster::where('id',$masterId)
            ->first(['id','document_number','price_list']);
        
        $customers = Customer::where('route_id', $routeId)
            ->where('group', $group)            
            ->get(['id','customer_name','group']);            
        
        $priceList = $master->price_list;
        $customerIds = $customers->pluck('id');
        // dd($customerIds);

        $routeName = MRoute::where('id',$routeId)->value('name');
        $narration = sprintf("%s %s 04012026", $routeName, $group === "Distributor" ? "dealers" : "shops");

        PriceMaster::create([
            'document_number' => $this->generatePriceMasterNumber(),
            'document_date'   => date('Y-m-d'),
            'effect_date'     => '2026-04-01',
            'narration'       => $narration,
            'customer_ids'    => $customerIds,
            'price_list'      => $priceList,            
            'status'          => PriceMasterStatus::ACTIVE,
        ]);

        echo sprintf("Price Master '%s' created successfully", $narration);
    }
*/
    public function createPriceMasters(Request $request)
    {
        $routeIds = [1,2,3,4,6,7,8,9,11,12,13,14,15,16,17,18,19,20,27];
        $groups   = ["Distributor", "Retailer"];

        $created = [];        

        foreach ($routeIds as $routeId) {
            foreach ($groups as $group) {
                // Pick masterId based on group
                $masterId = $group === "Distributor" ? 4 : 48;

                $master = PriceMaster::where('id', $masterId)
                    ->first(['id','document_number','price_list']);

                $priceList = $master->price_list;

                $customers = Customer::where('route_id', $routeId)
                    ->where('group', $group)
                    ->get(['id','customer_name','group']);

                $customerIds = $customers->pluck('id');
                // Skip if no customers found
                if ($customerIds->isEmpty()) {
                    continue;
                }

                $routeName   = MRoute::where('id', $routeId)->value('name');

                $narration   = sprintf(
                    "%s %s 01042026",
                    $routeName,
                    $group === "Distributor" ? "dealers" : "shops",
                );

                PriceMaster::create([
                    'document_number' => $this->generatePriceMasterNumber(),
                    'document_date'   => date('Y-m-d'),
                    'effect_date'     => '2026-04-01',
                    'narration'       => $narration,
                    'customer_ids'    => $customerIds,
                    'price_list'      => $priceList,
                    'status'          => PriceMasterStatus::ACTIVE,
                ]);

                $created[] = $narration;
            }
        }

        echo "Price Masters created successfully:<br>" . implode("<br>", $created);
    }

    private function generatePriceMasterNumber(): string
    {
        $last = PriceMaster::latest('id')->value('document_number');
        $number = $last ? intval(substr($last, 4)) + 1 : 1;
        return 'PRM-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
