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
        Schema::create('usuarios_en_cola', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("usuario_en_cola")->nullable();
            $table->foreign("usuario_en_cola")->references("id")->on("users")->restrictOnDelete();

            $table->string("nombre_usuario_anonimo")->nullable();

            $table->unsignedBigInteger("establecimiento_cola");
            $table->foreign("establecimiento_cola")->references("id")->on("establecimientos")->restrictOnDelete();

            $table->boolean("aplazada")->default(false);
            $table->dateTime("momentoestimado")->useCurrent()->index();

            $table->boolean("activo")->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_en_cola');
    }
};
