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
        Schema::create('price_masters', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->date('document_date');
            $table->date('effect_date');
            $table->string('narration');
            $table->json('customer_ids');
            $table->json('price_list');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('price_masters')
                ->nullOnDelete();
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'SUPERSEDED', 'SCHEDULED'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_masters');
    }
};
