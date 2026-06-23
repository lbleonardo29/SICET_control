<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empleado;
use App\Models\Planta;
use App\Models\Corp\EmpleadoTicket;
use App\Models\Corp\PlantaTicket;
use App\Support\EmpleadoMapper;

/**
     * Sincroniza el directorio maestro (plantas + empleados) desde la base corporativa
 * `tickets` hacia el espejo local de SICET.
 * * Idempotente: usa updateOrCreate por `numero_empleado` (= id_emp), lo que PRESERVA
 * el `id` autoincrement local y por tanto no rompe las FK ni el historial de asignaciones.
 * Las bajas se marcan activo=0 (nunca se borran empleados con historial).
 */
class SyncEmpleados extends Command
{
    protected $signature = 'sicet:sync-empleados {--dry-run : No persiste cambios} {--solo-plantas} {--solo-empleados}';

    protected $description = 'Sincroniza plantas y empleados desde la base corporativa `tickets` al espejo local.';

    public function handle(): int
    {
        if (!config('services.tickets.sync_enabled')) {
            $this->warn('Sincronización deshabilitada (TICKETS_SYNC_ENABLED=false). Nada que hacer.');
            return self::SUCCESS;
        }

        $dry = (bool) $this->option('dry-run');
        if ($dry) {
            $this->info('== DRY-RUN: no se persistirá ningún cambio ==');
        }

        try {
            if ($this->option('solo-empleados')) {
                // Reusar el mapa de plantas ya enlazadas localmente.
                $mapaPlantas = Planta::whereNotNull('id_planta_corp')->pluck('id', 'id_planta_corp')->all();
            } else {
                $mapaPlantas = $this->syncPlantas($dry);
            }

            if (!$this->option('solo-plantas')) {
                $this->syncEmpleados($dry, $mapaPlantas);
            }
        } catch (\Throwable $e) {
            $this->error('Error de sincronización: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Sincronización finalizada.');
        return self::SUCCESS;
    }

    /** Upsert de plantas; devuelve mapa [id_planta_corp => planta_id_local]. */
    private function syncPlantas(bool $dry): array
    {
        $creadas = 0;
        $actualizadas = 0;
        $mapa = [];

        foreach (PlantaTicket::all() as $corp) {
            $local = Planta::where('id_planta_corp', $corp->id_planta)->first();

            if (!$local) {
                // Enlazar por nombre normalizado si aún no tiene puente.
                $local = Planta::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim((string) $corp->nombre))])->first();
            }

            if ($local) {
                if (!$dry) {
                    $local->update([
                        'nombre' => $corp->nombre,
                        'id_planta_corp' => $corp->id_planta,
                    ]);
                }
                $actualizadas++;
            } else {
                if (!$dry) {
                    $local = Planta::create([
                        'nombre' => $corp->nombre,
                        'id_planta_corp' => $corp->id_planta,
                    ]);
                }
                $creadas++;
            }

            if ($local) {
                $mapa[$corp->id_planta] = $local->id;
            }
        }

        $this->info("Plantas -> creadas: {$creadas}, actualizadas: {$actualizadas}");
        return $mapa;
    }

    /** Upsert de empleados por numero_empleado (= id_emp). */
    private function syncEmpleados(bool $dry, array $mapaPlantas): void
    {
        $creados = 0;
        $actualizados = 0;
        $omitidos = 0;
        $vistos = [];

        EmpleadoTicket::query()->orderBy('id_emp')->chunk(500, function ($lote) use (
            &$creados, &$actualizados, &$omitidos, &$vistos, $mapaPlantas, $dry
        ) {
            foreach ($lote as $corp) {
                $num = (string) $corp->id_emp;
                $vistos[] = $num;

                $plantaIdLocal = isset($corp->id_planta) ? ($mapaPlantas[$corp->id_planta] ?? null) : null;

                $existe = Empleado::where('numero_empleado', $num)->exists();

                if (!$dry) {
                    // updateOrCreate por numero_empleado: preserva el id local (no rompe FK/CASCADE).
                    Empleado::updateOrCreate(
                        ['numero_empleado' => $num],
                        EmpleadoMapper::toSicet($corp, $plantaIdLocal)
                    );
                }

                $existe ? $actualizados++ : $creados++;
            }
        });

        // Bajas: empleados locales ausentes del snapshot -> inactivar (nunca borrar).
        $inactivados = 0;
        if (!$dry && !empty($vistos)) {
            $inactivados = Empleado::whereNotIn('numero_empleado', $vistos)
                ->where('activo', 1)
                ->update(['activo' => 0]);
        }

        $this->info("Empleados -> creados: {$creados}, actualizados: {$actualizados}, omitidos: {$omitidos}, inactivados: {$inactivados}");
    }
}
