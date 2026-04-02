<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\GstType;
use App\Enums\TcsStatus;
use App\Enums\TdsStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // Profile Information
            $table->string('name', 100)->unique();
            $table->string('code', 20)->unique();

            $table->text('address');
            $table->string('city', 50);
            
            $table->foreignId('state_id')
                ->constrained('states')
                ->restrictOnDelete();

            $table->string('landmark')->nullable();
            $table->string('pin_code', 6);

            $table->string('contact_number', 15)->nullable();
            $table->string('email', 100)->nullable();

            // Finance Information
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->unsignedSmallInteger('credit_days')->nullable();

            $table->enum('gst_type', array_column(GstType::cases(), 'value'));
            $table->string('gstin', 15)->nullable();

            $table->string('pan', 10)->nullable();
            $table->string('payment_terms')->nullable();

            $table->enum('tcs_status', array_column(TcsStatus::cases(), 'value'));
            $table->enum('tds_status', array_column(TdsStatus::cases(), 'value'));

            // Banking Information
            $table->foreignId('bank_id')
                ->constrained('banks')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('bank_branches')
                ->restrictOnDelete();

            $table->string('ifsc', 11);
            $table->string('account_holder', 100);
            $table->string('account_number', 30);

            // Tally Integration
            $table->enum('tally_sync_status', ['UNSYNCED', 'SYNCED', 'RESYNC'])
                  ->default('UNSYNCED');

            // Status
            $table->enum('status', ['ACTIVE', 'INACTIVE'])
                  ->default('ACTIVE');

            // Audit
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->restrictOnDelete();
            
            $table->timestamps();

            // Indexes (Performance-Oriented)
            $table->index('status');
            $table->index('tally_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
};
