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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 20)->unique();
            $table->date('document_date');
            $table->enum('status', ['PENDING', 'CANCELLED', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('created_by'); // FK to users.id
            $table->unsignedBigInteger('updated_by')->nullable(); // FK to users.id
            $table->unsignedBigInteger('actioned_by')->nullable(); // FK to users.id

            $table->timestamps();
            $table->timestamp('actioned_at')->nullable();

            // Indexes
            $table->index('document_date');
            $table->index('status');

            // Foreign keys
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('actioned_by')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
