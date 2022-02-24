<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id()->unique();
            $table->string('name')->default("Untitled");
            $table->string('url');
            $table->unsignedBigInteger('owner_id');
            $table->json('users');
            $table->foreign('owner_id')->references('id')
            ->on("users")->delete("cascade")->update("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
};
