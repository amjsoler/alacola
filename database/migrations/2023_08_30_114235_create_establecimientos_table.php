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
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->string("nombre", 100)->index();
            $table->string("logo")->nullable();
            $table->string("direccion", 255)->nullable()->index();
            //TODO: Poner index en desc
            $table->string("descripcion", 5000)->nullable();

            $table->unsignedBigInteger("usuario_administrador");
            $table->foreign("usuario_administrador")->references("id")->on("users")->restrictOnDelete();

            $table->string('latitud')->nullable();
            $table->string('longitud')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establecimientos');
    }
};
