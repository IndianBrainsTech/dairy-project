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
            "CREATE VIEW product_units_view AS 
                SELECT product_id,name,unit_id,unit_name,price,prim_unit,conversion
                from product_units,products,units
                WHERE product_units.product_id=products.id AND product_units.unit_id=units.id
                ORDER BY product_id ASC, prim_unit DESC"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW product_units_view");
    }
};
