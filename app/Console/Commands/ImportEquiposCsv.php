<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Models\Planta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEquiposCsv extends Command
{
    protected $signature = 'equipos:import {archivo} {--dry-run}';

    protected $description = 'Importa equipos desde un CSV (NombreEquipo,Marca,Modelo,NumeroSerie,DireccionMAC,Procesador,RAM,TipoAlmacenamiento,CapacidadAlmacenamiento,Color,Cargador,FechaAdquisicion,Planta,Observaciones)';

    public function handle()
    {
        $path = $this->argument('archivo');
        $dryRun = (bool) $this->option('dry-run');

        if (!file_exists($path)) {
            $this->error("No se encontró el archivo: {$path}");
            return 1;
        }

        $plantas = Planta::pluck('id', 'nombre')
            ->mapWithKeys(fn ($id, $nombre) => [strtoupper(trim($nombre)) => $id])
            ->all();

        $seriesExistentes = Equipo::pluck('numero_serie')
            ->map(fn ($s) => strtoupper(trim($s)))
            ->flip()
            ->all();

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        $aInsertar = [];
        $conProblemas = [];
        $seriesEnCsv = [];
        $macsEnCsv = [];

        // Primera pasada: contar MACs repetidas dentro del propio CSV
        $filas = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // fila vacía
            }
            $filas[] = array_combine($header, array_pad($row, count($header), ''));
        }
        fclose($handle);

        foreach ($filas as $fila) {
            $mac = strtoupper(trim($fila['DireccionMAC'] ?? ''));
            if ($mac !== '' && $mac !== 'N/A') {
                $macsEnCsv[$mac] = ($macsEnCsv[$mac] ?? 0) + 1;
            }
        }

        foreach ($filas as $i => $fila) {
            $numeroFila = $i + 2; // +1 por índice 0, +1 por el header

            $nombreEquipo = strtoupper(trim($fila['NombreEquipo'] ?? ''));
            $marca = strtoupper(trim($fila['Marca'] ?? ''));
            $modelo = strtoupper(trim($fila['Modelo'] ?? ''));
            $numeroSerie = strtoupper(trim($fila['NumeroSerie'] ?? ''));
            $mac = strtoupper(trim($fila['DireccionMAC'] ?? ''));
            $procesador = strtoupper(trim($fila['Procesador'] ?? ''));
            $ram = strtoupper(trim($fila['RAM'] ?? ''));
            $tipoAlm = strtoupper(trim($fila['TipoAlmacenamiento'] ?? ''));
            $capAlm = strtoupper(trim($fila['CapacidadAlmacenamiento'] ?? ''));
            $color = strtoupper(trim($fila['Color'] ?? ''));
            $cargadorTxt = strtoupper(trim($fila['Cargador'] ?? ''));
            $fechaAdq = trim($fila['FechaAdquisicion'] ?? '');
            $plantaTxt = strtoupper(trim($fila['Planta'] ?? ''));
            $observaciones = trim($fila['Observaciones'] ?? '');

            $motivos = [];

            if ($modelo === '') {
                $modelo = 'SIN DATO';
                $motivos[] = 'modelo vacío -> placeholder "SIN DATO"';
            }

            if ($numeroSerie === '') {
                $numeroSerie = 'PENDIENTE-' . $nombreEquipo;
                $motivos[] = 'numero_serie vacío -> placeholder "' . $numeroSerie . '"';
            }

            if (isset($seriesExistentes[$numeroSerie])) {
                $motivos[] = "numero_serie '{$numeroSerie}' ya existe en la base de datos";
            }

            if (isset($seriesEnCsv[$numeroSerie])) {
                $motivos[] = "numero_serie '{$numeroSerie}' repetido dentro del propio CSV (fila {$seriesEnCsv[$numeroSerie]})";
            }

            if (!isset($plantas[$plantaTxt])) {
                $motivos[] = "planta '{$plantaTxt}' no existe en la tabla plantas";
            }

            if ($mac !== '' && $mac !== 'N/A' && ($macsEnCsv[$mac] ?? 0) > 1) {
                $motivos[] = "MAC '{$mac}' repetida dentro del propio CSV";
            }

            $cargador = match ($cargadorTxt) {
                'SI', 'SÍ', '1', 'TRUE' => true,
                'NO', '0', 'FALSE' => false,
                default => true,
            };

            $datos = [
                'nombre_equipo' => $nombreEquipo,
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => $numeroSerie,
                'direccion_mac' => ($mac === '' || $mac === 'N/A') ? null : $mac,
                'color' => $color === '' ? null : $color,
                'procesador' => $procesador,
                'ram' => $ram,
                'tipo_almacenamiento' => $tipoAlm === '' ? null : $tipoAlm,
                'capacidad_almacenamiento' => $capAlm === '' ? null : $capAlm,
                'ssd' => trim($tipoAlm . ' ' . $capAlm),
                'cargador' => $cargador,
                'fecha_adquisicion' => $fechaAdq === '' ? null : $fechaAdq,
                'planta_id' => $plantas[$plantaTxt] ?? null,
                'observaciones' => $observaciones === '' ? null : $observaciones,
                'estado' => 'Disponible',
            ];

            if (!empty($motivos) && isset($plantas[$plantaTxt]) === false) {
                // Sin planta no se puede insertar; el resto de motivos son advertencias que no bloquean
                $conProblemas[] = ['fila' => $numeroFila, 'nombre' => $nombreEquipo, 'motivos' => $motivos, 'bloqueante' => true];
                continue;
            }

            if (isset($seriesExistentes[$numeroSerie]) || isset($seriesEnCsv[$numeroSerie])) {
                $conProblemas[] = ['fila' => $numeroFila, 'nombre' => $nombreEquipo, 'motivos' => $motivos, 'bloqueante' => true];
                continue;
            }

            $seriesEnCsv[$numeroSerie] = $numeroFila;

            if (!empty($motivos)) {
                $conProblemas[] = ['fila' => $numeroFila, 'nombre' => $nombreEquipo, 'motivos' => $motivos, 'bloqueante' => false];
            }

            $aInsertar[] = $datos;
        }

        $this->info('--- Resumen ---');
        $this->info('Filas listas para insertar: ' . count($aInsertar));
        $this->info('Filas con advertencia (se insertan igual): ' . count(array_filter($conProblemas, fn ($p) => !$p['bloqueante'])));
        $this->info('Filas bloqueadas (NO se insertan): ' . count(array_filter($conProblemas, fn ($p) => $p['bloqueante'])));

        if (!empty($conProblemas)) {
            $this->line('');
            $this->line('Detalle:');
            foreach ($conProblemas as $p) {
                $tag = $p['bloqueante'] ? '[BLOQUEADA]' : '[advertencia]';
                $this->line("  {$tag} fila {$p['fila']} ({$p['nombre']}): " . implode('; ', $p['motivos']));
            }
        }

        if ($dryRun) {
            $this->line('');
            $this->info('Modo --dry-run: no se insertó nada en la base de datos.');
            return 0;
        }

        DB::transaction(function () use ($aInsertar) {
            foreach ($aInsertar as $datos) {
                Equipo::create($datos);
            }
        });

        $this->line('');
        $this->info(count($aInsertar) . ' equipos insertados correctamente.');

        return 0;
    }
}
