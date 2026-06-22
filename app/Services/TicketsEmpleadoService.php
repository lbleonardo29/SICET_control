<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Planta;
use App\Models\Corp\EmpleadoTicket;
use App\Support\EmpleadoMapper;

/**
 * Write-through hacia la base corporativa `tickets`: cuando SICET crea/edita/da de baja
 * un empleado, refleja el cambio en tbl_empleados.
 *
 * Lanza excepción si la conexión/escritura falla; el llamador (EmpleadoController) la
 * captura y degrada de forma elegante (patrón local-first): SICET nunca se bloquea.
 */
class TicketsEmpleadoService
{
    /** ¿Está activado el write-through? (TICKETS_WRITE_THROUGH) */
    public function enabled(): bool
    {
        return (bool) config('services.tickets.write_through');
    }

    /**
     * Crea o actualiza el empleado en tbl_empleados (corp). id_emp = numero_empleado.
     * En update NO toca columnas no gestionadas por SICET (contrasenia, id_rol, area...).
     */
    public function pushUpsert(Empleado $empleado): void
    {
        if (!$this->enabled()) {
            return;
        }

        $idEmp = (int) $empleado->numero_empleado;
        $idPlantaCorp = $this->resolverIdPlantaCorp($empleado->planta_id);
        $datos = EmpleadoMapper::toCorp($empleado, $idPlantaCorp);

        $existente = EmpleadoTicket::find($idEmp);

        if ($existente) {
            $existente->fill($datos)->save();
        } else {
            EmpleadoTicket::create(array_merge(['id_emp' => $idEmp], $datos));
        }
    }

    /** Cambia solo el estado activo ('S'/'N') del empleado en el corporativo. */
    public function pushActivo(string $numeroEmpleado, bool $activo): void
    {
        if (!$this->enabled()) {
            return;
        }

        $corp = EmpleadoTicket::find((int) $numeroEmpleado);
        if ($corp) {
            $corp->activo = EmpleadoMapper::activoToCorp($activo);
            $corp->save();
        }
    }

    /** Resuelve el id_planta del corporativo a partir del planta_id local (vía columna puente). */
    private function resolverIdPlantaCorp(?int $plantaIdLocal): ?int
    {
        if (!$plantaIdLocal) {
            return null;
        }

        $valor = Planta::whereKey($plantaIdLocal)->value('id_planta_corp');
        return $valor !== null ? (int) $valor : null;
    }
}
