<?php

namespace App\Support;

/**
 * Traducción de columnas en la frontera entre el esquema corporativo (`tickets`)
 * y el esquema local de SICET. Métodos puros, sin acceso a BD (fáciles de testear).
 *
 * Pivote estable entre sistemas: empleados.numero_empleado (SICET) == tbl_empleados.id_emp (tickets).
 */
class EmpleadoMapper
{
    /** Concatena nombre + apellidos del lado corporativo en un solo nombre_completo. */
    public static function nombreCompleto(?string $nombre, ?string $apellidos): string
    {
        return trim(trim((string) $nombre) . ' ' . trim((string) $apellidos));
    }

    /**
     * Parte un nombre_completo en nombre (1ª palabra) + apellidos (resto).
     * Heurística: tbl_empleados separa nombre/apellidos pero SICET guarda uno solo.
     */
    public static function splitNombre(?string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim((string) $nombreCompleto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $nombre = array_shift($partes) ?? '';
        $apellidos = implode(' ', $partes);

        return ['nombre' => $nombre, 'apellidos' => $apellidos];
    }

    /** 'S'/'N' (u otros) del corporativo -> booleano de SICET. */
    public static function activoToBool($valor): bool
    {
        return strtoupper(trim((string) $valor)) === 'S';
    }

    /** Booleano/entero de SICET -> 'S'/'N' del corporativo. */
    public static function activoToCorp($valor): string
    {
        return $valor ? 'S' : 'N';
    }

    /**
     * Fila de tbl_empleados (corp) -> atributos para empleados (SICET).
     * $plantaIdLocal ya debe estar resuelto (vía plantas.id_planta_corp).
     */
    public static function toSicet(object $corp, ?int $plantaIdLocal): array
    {
        return [
            'numero_empleado' => (string) $corp->id_emp,
            'nombre_completo' => self::nombreCompleto($corp->nombre ?? null, $corp->apellidos ?? null),
            'correo'          => $corp->email ?? null,
            'planta_id'       => $plantaIdLocal,
            'activo'          => self::activoToBool($corp->activo ?? null),
        ];
    }

    /**
     * Empleado (SICET) -> columnas gestionadas de tbl_empleados (corp).
     * $idPlantaCorp ya debe estar resuelto (vía plantas.id_planta_corp).
     * NO incluye contrasenia/id_rol/temporal/politica/area (las gestiona el corporativo).
     */
    public static function toCorp(object $empleado, ?int $idPlantaCorp): array
    {
        $split = self::splitNombre($empleado->nombre_completo ?? null);

        return [
            'nombre'    => $split['nombre'],
            'apellidos' => $split['apellidos'],
            'email'     => $empleado->correo ?? null,
            'activo'    => self::activoToCorp($empleado->activo ?? false),
            'id_planta' => $idPlantaCorp,
        ];
    }
}
