<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', places: 2);
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('to_user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('buy_article_transactions', function (Blueprint $table) {
            $table->foreignId('transaction_id')->primary()->constrained();
            $table->foreignId('article_id')->constrained();
        });

        Schema::create('undo_transactions', function (Blueprint $table) {
            $table->foreignId('transaction_id')->primary()->constrained();
            $table->foreignId('undone_transaction_id')->constrained('transactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_article_transactions');
        Schema::dropIfExists('undo_transactions');
        Schema::dropIfExists('transactions');
    }
};
