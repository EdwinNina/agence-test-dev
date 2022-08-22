<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(){
        $consultores = DB::table('cao_usuario as usuarios')
            ->join('permissao_sistema as permisos', 'usuarios.co_usuario', 'permisos.co_usuario')
            ->where([
                ['co_sistema', '=', '1'],
                ['in_ativo', '=', 'S'],
            ])
            ->whereIn('co_tipo_usuario', [0, 1, 2])
            ->get();
        return view('dashboard', compact('consultores'));
    }
}
