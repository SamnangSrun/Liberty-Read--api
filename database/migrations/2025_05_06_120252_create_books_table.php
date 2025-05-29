<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('author');
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->string('cover_image')->nullable();

            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

            $table->enum('status', ['pending', 'approved', 'disapproved'])->default('pending');

            $table->text('reject_note')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
}
