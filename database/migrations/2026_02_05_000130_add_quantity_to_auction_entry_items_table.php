<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_entry_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('subject_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('auction_entry_items', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
