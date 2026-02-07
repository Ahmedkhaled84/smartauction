<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_item_id')->constrained('subject_items')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_entry_items');
    }
};
