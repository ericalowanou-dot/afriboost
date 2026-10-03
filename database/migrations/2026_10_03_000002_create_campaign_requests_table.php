<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Demandes de campagne envoyées par les marques (clients)
        Schema::create('campaign_requests', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('objective');
            $table->json('networks')->nullable();
            $table->decimal('budget_usd', 12, 2)->nullable();
            $table->date('desired_start')->nullable();
            $table->string('status')->default('new'); // new | contacted | converted | declined
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_requests');
    }
};
