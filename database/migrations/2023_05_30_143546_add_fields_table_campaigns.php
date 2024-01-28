<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->integer('available_tickets')->default(0);
            $table->integer('pending_tickets')->default(0);
            $table->integer('unavailable_tickets')->default(0);
        });

        // Atualiza o valor padrão para 'available_tickets' usando o valor da coluna 'amount_tickets'
        DB::statement('UPDATE campaigns SET available_tickets = amount_tickets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('available_tickets');
            $table->dropColumn('pending_tickets');
            $table->dropColumn('unavailable_tickets');
        });
    }
};
