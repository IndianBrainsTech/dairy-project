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
        Schema::create('purchase_item_units', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();
            $table->string('abbreviation', 10)->unique();
            $table->char('hot_key', 1)->unique();

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
                  ->nullOnDelete();

            $table->timestamps();

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
        Schema::dropIfExists('purchase_item_units');
    }
};
