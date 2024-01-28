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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 125);
            $table->string('description', 255);
            $table->string('image')->nullable();
            $table->string('amount_tickets');
            $table->string('support_number');
            $table->integer('status');
            $table->integer('status_payment');
            $table->string('price_each_ticket');
            $table->integer('min_ticket');
            $table->integer('max_ticket');
            $table->boolean('show_date_of_raffle');
            $table->dateTime('date_of_raffle');
            $table->integer('time_wait_payment');
            $table->boolean('allow_terms');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('ticket_filter_id');
            $table->unsignedBigInteger('raffle_id');
            $table->unsignedBigInteger('fee_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('ticket_filter_id')->references('id')->on('ticket_filters')->onDelete('cascade');
            $table->foreign('raffle_id')->references('id')->on('raffles')->onDelete('cascade');
            $table->foreign('fee_id')->references('id')->on('fees')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
