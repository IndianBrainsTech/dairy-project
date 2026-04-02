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
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('credit_note_id')
                ->constrained('credit_notes')
                ->cascadeOnDelete();

            $table->string('invoice_number', 20);
            $table->string('invoice_date', 10);
            $table->decimal('invoice_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->nullable();
            $table->decimal('outstanding_amount', 15, 2);
            $table->decimal('adjusted_amount', 15, 2);

            $table->timestamps();

            // Indexes
            $table->index('credit_note_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_note_items');
    }
};
