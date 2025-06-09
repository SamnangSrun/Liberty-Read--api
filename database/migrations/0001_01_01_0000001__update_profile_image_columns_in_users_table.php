<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('profile_photo');
        $table->string('profile_image_url')->nullable();
        $table->string('profile_image_id')->nullable();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('profile_photo')->nullable();
        $table->dropColumn(['profile_image_url', 'profile_image_id']);
    });
}

};
