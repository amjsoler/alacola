<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UsuarioEnCola;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsuarioEnColaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        $usuarios = User::orderBy("id", "asc")->get();

        foreach($usuarios as $usuario){
            $userEnCola = new UsuarioEnCola();
            $userEnCola->usuario_en_cola = $usuario->id;
            $userEnCola->momentoestimado = $faker->dateTimeBetween("-2 weeks", "now");
            $userEnCola->establecimiento_cola = 1;
            $userEnCola->save();

            $userEnCola = new UsuarioEnCola();
            $userEnCola->usuario_en_cola = $usuario->id;
            $userEnCola->momentoestimado = $faker->dateTimeBetween("-2 weeks", "now");
            $userEnCola->establecimiento_cola = 2;
            $userEnCola->save();

            $userEnCola = new UsuarioEnCola();
            $userEnCola->usuario_en_cola = $usuario->id;
            $userEnCola->momentoestimado = $faker->dateTimeBetween("-2 weeks", "now");
            $userEnCola->establecimiento_cola = 3;
            $userEnCola->save();
        }
    }
}
