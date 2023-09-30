<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;

class EstablecimientoController extends Controller
{
    public function buscarEstablecimientos(Request $request)
    {
        //TODO: Deberían los campos dirección y descripción ser indices para buscar más rapido sobre ellos???
        $resultadosBusqueda = Establecimiento::where("nombre", "like", "%".$request->get("busqueda")."%")
            ->orWhere("direccion", "like", "%".$request->get("busqueda")."%")
            ->orWhere("descripcion", "like", "%".$request->get("busqueda")."%")
            ->get();

        return response($resultadosBusqueda);
    }

    public function verEstablecimiento(Establecimiento $establecimiento)
    {
        return response($establecimiento);
    }
}
