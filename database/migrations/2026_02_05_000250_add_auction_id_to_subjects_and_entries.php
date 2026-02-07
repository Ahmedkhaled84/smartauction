<?php

use App\Models\Auction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('auction_id')->nullable()->constrained('auctions')->cascadeOnDelete();
        });

        Schema::table('auction_entries', function (Blueprint $table) {
            $table->foreignId('auction_id')->nullable()->constrained('auctions')->cascadeOnDelete();
        });

        $auction = Auction::first();
        if (!$auction) {
            $auctionId = DB::table('auctions')->insertGetId([
                'code' => '841999',
                'start_date' => '2026-02-04',
                'end_date' => '2026-02-06',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $auctionId = $auction->id;
        }

        DB::table('subjects')->update(['auction_id' => $auctionId]);
        DB::table('auction_entries')->update(['auction_id' => $auctionId]);
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auction_id');
        });

        Schema::table('auction_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auction_id');
        });
    }
};
