<?php

// database/migrations/xxxx_xx_xx_create_seller_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerRequestsTable extends Migration
{
   public function up()
{
    Schema::create('seller_requests', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('seller_id');
        $table->string('status');
        $table->string('name'); // Add the name column
        $table->date('birthdate'); // Add birthdate column
        $table->string('phone_number'); // Add phone number column
        $table->text('rejection_note')->nullable(); // Add rejection note column
        $table->timestamps();

        $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('seller_requests');
}


}
