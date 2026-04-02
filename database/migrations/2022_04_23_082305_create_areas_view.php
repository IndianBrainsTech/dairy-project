<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "CREATE VIEW areas_view as 
                SELECT areas.id as area_id,areas.name as area,routes.name as route,districts.name as district,states.name as state 
                from areas,routes,districts,states 
                WHERE areas.route_id=routes.id AND routes.district_id=districts.id AND districts.state_id=states.id"                
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW areas_view");
    }
};
