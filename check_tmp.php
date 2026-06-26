<?php

use App\Models\User;
use App\Models\Asignacion;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "Asignaciones aceptadas de Edgar (emp 9):" . PHP_EOL;
foreach (Asignacion::where('empleado_id', 9)->where('estado_asignacion', 'aceptada')->get() as $a) {
    echo "  #{$a->id} equipo={$a->equipo_id} carta_pdf=" . ($a->carta_pdf ?? 'NULL') . PHP_EOL;
}

$edgar = User::where('numero_empleado', '2360')->first();
Auth::login($edgar);
$req = Request::create('/dashboard', 'GET');
app()->instance('request', $req);
$html = app(AdminController::class)->dashboard($req)->render();
echo "Dashboard trae 'Descargar carta (PDF)': " . (str_contains($html, 'Descargar carta (PDF)') ? 'si' : 'no') . PHP_EOL;
echo "Trae ruta descargar: " . (str_contains($html, '/asignaciones/descargar/') ? 'si' : 'no') . PHP_EOL;
