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
            CREATE TRIGGER prevent_duplicate_incentive
            BEFORE INSERT ON incentives
            FOR EACH ROW
            BEGIN
                IF (NEW.incentive_status = 'Pending') AND 
                    EXISTS (
                        SELECT 1 FROM incentives 
                        WHERE customer_id = NEW.customer_id
                        AND from_date = NEW.from_date
                        AND to_date = NEW.to_date
                        AND incentive_status = 'Pending'
                    )
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Duplicate Incentive!';
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
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_duplicate_incentive");
    }
};
