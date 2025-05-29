<?php

// database/migrations/xxxx_xx_xx_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{


// database/migrations/xxxx_xx_xx_create_orders_table.php
public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->decimal('total_price', 10, 2);
        $table->enum('order_status', ['pending', 'processing', 'shipped', 'delivered', 'canceled'])->default('pending');
        $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
        $table->timestamps();
    });

   
    
}


public function down()
{
    Schema::dropIfExists('orders');
}

}
