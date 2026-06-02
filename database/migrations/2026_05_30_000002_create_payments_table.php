<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->decimal('amount_total', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('seller_amount', 10, 2)->default(0);
            $table->unsignedTinyInteger('commission_rate')->default(10);
            $table->enum('status', ['pending', 'paid', 'transferred', 'failed', 'refunded'])->default('pending');
            $table->json('stripe_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};
