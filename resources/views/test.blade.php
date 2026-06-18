<!DOCTYPE html>
<html>
<head>
    <title>Asignar Equipo</title>
</head>
<body>

<h2>Asignar Equipo</h2>

{{-- Mensaje éxito --}}
@if(session('success'))
    <p style="color: green; font-weight: bold;">
        {{ session('success') }}
    </p>
@endif

{{-- Mensaje error --}}
@if(session('error'))
    <p style="color: red; font-weight: bold;">
        {{ session('error') }}
    </p>
@endif

<form method="POST" action="{{ route('asignaciones.store') }}">
    @csrf

    <label>Equipo:</label>
    <select name="equipo_id" required>
        <option value="">-- Selecciona un equipo --</option>
        @forelse($equipos as $equipo)
            <option value="{{ $equipo->id }}">
                {{ $equipo->codigo_interno }} - 
                {{ $equipo->marca }} {{ $equipo->modelo }}
            </option>
        @empty
            <option disabled>No hay equipos disponibles</option>
        @endforelse
    </select>
    <br><br>

    <label>Empleado:</label>
    <select name="empleado_id" required>
        <option value="">-- Selecciona un empleado --</option>
        @forelse($empleados as $empleado)
            <option value="{{ $empleado->id }}">
                {{ $empleado->nombre_completo }}
            </option>
        @empty
            <option disabled>No hay empleados registrados</option>
        @endforelse
    </select>
    <br><br>

    <button type="submit">Asignar</button>
</form>

<hr>

<h2>Asignaciones Activas</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Código</th>
        <th>Equipo</th>
        <th>Empleado</th>
        <th>Fecha Asignación</th>
        <th>Acción</th>
    </tr>

    @forelse($asignaciones as $asignacion)
        <tr>
            <td>{{ $asignacion->equipo->codigo_interno }}</td>
            <td>
                {{ $asignacion->equipo->marca }}
                {{ $asignacion->equipo->modelo }}
            </td>
            <td>{{ $asignacion->empleado->nombre_completo }}</td>
            <td>{{ $asignacion->fecha_asignacion }}</td>
            <td>
                <form method="POST" action="{{ route('asignaciones.devolver', $asignacion->id) }}">
                    @csrf
                    <button type="submit">Devolver</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5">No hay asignaciones activas</td>
        </tr>
    @endforelse
</table>


</body>
</html>
