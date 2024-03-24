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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoryId');
            $table->string('slug')->unique();
            $table->string('nameEng');
            $table->string('nameInd');
            $table->string('thumbnailUrl')->nullable();
            $table->string('nameEngDesc')->nullable();
            $table->string('nameIndDesc')->nullable();
            $table->string('nameIndContent')->nullable();
            $table->timestamps();

            $table->foreign('categoryId')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
