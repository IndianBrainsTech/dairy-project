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
        // Trigger to prevent inserting a duplicate receipt
        DB::unprepared("
            CREATE TRIGGER prevent_duplicate_receipt
            BEFORE INSERT ON receipts
            FOR EACH ROW
            BEGIN
                IF (NEW.status = 'Pending') AND 
                    EXISTS (
                        SELECT 1 FROM receipts
                        WHERE receipt_date = NEW.receipt_date 
                        AND customer_id = NEW.customer_id
                        AND mode = NEW.mode
                        AND amount = NEW.amount
                    )
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Duplicate Receipt!';
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
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_duplicate_receipt");
    }
};
