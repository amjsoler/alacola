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
        Schema::create('establecimientos_favoritos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("usuario_id");
            $table->foreign("usuario_id")->references("id")->on("users");

            $table->unsignedBigInteger("establecimiento_id");
            $table->foreign("establecimiento_id")->references("id")->on("establecimientos");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establecimientos_favoritos');
    }
};
