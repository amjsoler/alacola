<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Database\Seeder;

class EstablecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idUserPruebas = User::where("email", "amjsoler@gmail.com")->first()->id;

        Establecimiento::factory()->count(10)->create([
            'usuario_administrador' => $idUserPruebas
        ]);
    }
}
