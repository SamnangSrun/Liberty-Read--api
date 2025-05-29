<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
    $table->id();

    // link payment to order
    $table->foreignId('order_id')->constrained()->onDelete('cascade');



    $table->enum('payment_method', ['card', 'cash']);
    $table->enum('payment_status', ['pending', 'completed', 'failed']);
    $table->string('transaction_id')->nullable();

    // customer details
    $table->string('first_name');
    $table->string('last_name');
    $table->string('country');
    $table->string('street_address');
    $table->string('town_city');
    $table->string('state_county');
    $table->string('postcode');
    $table->string('phone');
    $table->string('email');

    $table->text('order_notes')->nullable();

    $table->timestamps();
});

    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
