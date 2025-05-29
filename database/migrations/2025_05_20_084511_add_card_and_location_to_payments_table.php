<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->string('visa_card_number')->nullable();
        $table->string('visa_expiry_date')->nullable();
        $table->string('visa_cvc')->nullable();
        $table->string('location')->nullable();
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn(['visa_card_number', 'visa_expiry_date', 'visa_cvc', 'location']);
    });
}

};
