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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('nickname')->nullable()->change();
            $table->string('image')->nullable()->change();
            $table->string('text_delete_account')->nullable()->change();
            $table->unsignedBigInteger('payment_id')->nullable()->change();
            $table->unsignedBigInteger('social_media_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
