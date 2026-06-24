@extends('layouts.sicet')

@section('page-title', 'Gestión de Usuarios')
@section('page-subtitle', 'Asigna roles a los empleados que han accedido al sistema')

@section('content')

<div class="s-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="s-badge s-badge-green me-1">{{ $usuarios->total() }} usuarios registrados</span>
        </div>
        <small class="text-muted">Solo aparecen empleados que han iniciado sesión al menos una vez</small>
    </div>

    <div class="table-responsive">
        <table class="s-table">
            <thead>
                <tr>
                    <th>#Emp</th>
                    <th>Nombre</th>
                    <th>Rol actual</th>
                    <th>Cambiar rol</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                <tr>
                    <td>
                        <span class="fw-semibold" style="color:rgb(21,64,31)">
                            {{ $u->numero_empleado ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="s-initials" style="width:34px;height:34px;font-size:12px;flex-shrink:0">
                                {{ mb_strtoupper(mb_substr($u->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-medium" style="font-size:14px">{{ $u->name }}</div>
                                <div style="font-size:12px;color:rgb(130,136,124)">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($u->role === 'admin')
                            <span class="s-badge s-badge-green">Admin</span>
                        @elseif($u->role === 'rh')
                            <span class="s-badge s-badge-blue">RH</span>
                        @else
                            <span class="s-badge s-badge-gray">Usuario</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('usuarios.rol', $u->id) }}"
                              class="d-flex align-items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="role" class="form-select form-select-sm" style="width:130px"
                                    onchange="this.closest('form').submit()">
                                <option value="user"  {{ $u->role === 'user'  ? 'selected' : '' }}>Usuario</option>
                                <option value="rh"    {{ $u->role === 'rh'    ? 'selected' : '' }}>RH</option>
                                <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Ningún empleado ha iniciado sesión aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($usuarios->hasPages())
    <div class="mt-3">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>

@endsection
