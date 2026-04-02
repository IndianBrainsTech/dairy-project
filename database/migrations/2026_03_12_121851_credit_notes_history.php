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
        Schema::create('credit_notes_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('credit_note_id')
                ->constrained('credit_notes')
                ->cascadeOnDelete();

            $table->string('document_number', 10);
            $table->date('document_date');

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('reason', ['DISCOUNT', 'BAD_DEBT', 'ROUND_OFF']);
            $table->text('narration')->nullable();

            $table->decimal('amount', 15, 2)->default(0);

            $table->unsignedBigInteger('version_code');
            $table->unsignedBigInteger('user_id'); // FK to users.id            
            $table->enum('sql_action', ['CREATE', 'INSERT', 'UPDATE', 'DELETE']);

            $table->timestamps();
            
            // Composite Index
            $table->index(['credit_note_id', 'version_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_notes_history');
    }
};
