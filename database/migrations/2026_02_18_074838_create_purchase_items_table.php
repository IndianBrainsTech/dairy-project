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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name', 100)->unique();

            $table->foreignId('group_id')
                  ->constrained('purchase_item_groups')
                  ->restrictOnDelete();

            $table->string('hsn_code', 10);
            $table->foreign('hsn_code')
                  ->references('hsn_code')
                  ->on('hsn_codes')
                  ->restrictOnDelete();

            $table->enum('tax_type', ['TAXABLE', 'EXEMPTED']);

            $table->decimal('gst', 5,2)->nullable();
            $table->decimal('sgst', 5,2)->nullable();
            $table->decimal('cgst', 5,2)->nullable();
            $table->decimal('igst', 5,2)->nullable();

            $table->unsignedSmallInteger('sort_order')->nullable();

            $table->enum('tally_sync_status', ['UNSYNCED', 'SYNCED', 'RESYNC'])
                  ->default('UNSYNCED');

            $table->enum('status', ['ACTIVE', 'INACTIVE'])
                  ->default('ACTIVE');

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->timestamps();

            $table->index('group_id');
            $table->index('hsn_code');
            $table->index(['status', 'tally_sync_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
};
