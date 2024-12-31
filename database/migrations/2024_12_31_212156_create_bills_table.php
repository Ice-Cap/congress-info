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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('bill_id');
            $table->string('bill_type');
            $table->integer("congress_number");
            $table->string("bill_summary");
            $table->string("bill_title");
            $table->string("bill_ai_summary");
            $table->string("bill_full_text");
            $table->string("bill_latest_action");
            $table->string("bill_latest_action_date");
            $table->string("bill_update_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
