<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_transporters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('alt_phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('pan_number', 15)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 30)->nullable();
            $table->string('bank_ifsc', 15)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by', 'st_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'st_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
        });

        // Now add the FK from vehicles → supplier_transporters
        // (column was added in migration 100002, FK deferred until this table exists)
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'supplier_transporter_id')) {
                $table->foreign('supplier_transporter_id', 'v_supplier_fk')
                      ->references('id')->on('supplier_transporters')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign('v_supplier_fk');
        });

        Schema::dropIfExists('supplier_transporters');
    }
};
