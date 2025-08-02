<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bdsms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->enum('driver',['MimSms','twilio','sslwireless','infobip'])->index(); // e.g., 'MimSms', 'sslwireless', 'twilio', 'infobip'
            $table->string('to')->index();
            $table->longText('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index(); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->longText('error_message')->nullable(); // For storing error messages if sending fails
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at for soft deletes
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bdsms');
    }
};
