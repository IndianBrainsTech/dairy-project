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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();

            $table->string('document_number', 10)->unique();
            $table->date('document_date');

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('reason', ['DISCOUNT', 'BAD_DEBT', 'ROUND_OFF']);
            $table->text('narration')->nullable();

            $table->decimal('amount', 15, 2)->default(0);

            $table->enum('status', ['DRAFT', 'APPROVED', 'CANCELLED'])
                ->default('DRAFT');

            $table->string('cancel_remarks')->nullable();

            $table->unsignedBigInteger('current_version');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('actioned_by')->nullable();

            $table->timestamps();
            $table->timestamp('actioned_at')->nullable();

            // user foreign keys
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('actioned_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            // Indexes
            $table->index(['customer_id', 'status', 'document_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_notes');
    }
};
