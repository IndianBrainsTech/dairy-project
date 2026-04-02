<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Trigger to prevent inserting a second 'Not Generated' order
        DB::unprepared("
            CREATE TRIGGER prevent_duplicate_order
            BEFORE INSERT ON orders
            FOR EACH ROW
            BEGIN
                IF (NEW.invoice_status = 'Not Generated') AND 
                    EXISTS (
                        SELECT 1 FROM orders 
                        WHERE invoice_date = NEW.invoice_date 
                        AND customer_id = NEW.customer_id 
                        AND invoice_status = 'Not Generated'
                    )
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Pending Order Exists!';
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_duplicate_order");
    }
};
