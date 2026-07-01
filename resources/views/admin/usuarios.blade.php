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

    {{-- Búsqueda y filtro de rol --}}
    <form method="GET" class="row g-3 align-items-center mb-4">
        <div class="col-md-7">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0"
                       placeholder="Buscar por nombre, número de empleado o correo..."
                       value="{{ request('q') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administradores</option>
                <option value="rh"    {{ request('role') == 'rh'    ? 'selected' : '' }}>RH</option>
                <option value="user"  {{ request('role') == 'user'  ? 'selected' : '' }}>Usuarios</option>
            </select>
        </div>
        <div class="col-md-2">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if(request()->anyFilled(['q', 'role']))
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

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
                                    data-nombre="{{ $u->name }}"
                                    data-rol-actual="{{ $u->role }}"
                                    onchange="confirmarCambioRol(this)">
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
                        @if(request()->anyFilled(['q', 'role']))
                            No se encontraron usuarios con esa búsqueda o filtro.
                        @else
                            Ningún empleado ha iniciado sesión aún.
                        @endif
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

{{-- ===== MODAL: CONFIRMAR CAMBIO DE ROL ===== --}}
<div class="modal fade" id="modalConfirmarRol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-shield me-2"></i>Confirmar cambio de rol</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
          <i class="bi bi-question-circle fs-5 flex-shrink-0"></i>
          <span class="small">
            ¿Seguro que quieres cambiar el rol de <strong id="cr-nombre">—</strong>
            de <strong id="cr-rol-actual">—</strong> a <strong id="cr-rol-nuevo">—</strong>?
          </span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="btnCancelarCambioRol">
          <i class="bi bi-arrow-left me-1"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary px-4" id="btnConfirmarCambioRol">
          <i class="bi bi-check-circle me-2"></i> Confirmar
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    const etiquetasRolUsuario = { admin: 'Admin', rh: 'RH', user: 'Usuario' };
    let selectRolPendiente = null;

    function confirmarCambioRol(select) {
        const nuevoRol  = select.value;
        const rolActual = select.dataset.rolActual;

        if (nuevoRol === rolActual) return; // no hubo cambio real

        selectRolPendiente = select;

        document.getElementById('cr-nombre').textContent     = select.dataset.nombre;
        document.getElementById('cr-rol-actual').textContent = etiquetasRolUsuario[rolActual] || rolActual;
        document.getElementById('cr-rol-nuevo').textContent  = etiquetasRolUsuario[nuevoRol] || nuevoRol;

        new bootstrap.Modal(document.getElementById('modalConfirmarRol')).show();
    }

    function revertirSelectRol() {
        if (selectRolPendiente) {
            selectRolPendiente.value = selectRolPendiente.dataset.rolActual;
            selectRolPendiente = null;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalConfirmarRol');

        document.getElementById('btnCancelarCambioRol').addEventListener('click', function () {
            revertirSelectRol();
            bootstrap.Modal.getInstance(modalEl).hide();
        });

        document.getElementById('btnConfirmarCambioRol').addEventListener('click', function () {
            if (selectRolPendiente) {
                selectRolPendiente.closest('form').submit();
            }
        });

        // Si cierran el modal con la X o clic afuera, también se revierte.
        modalEl.addEventListener('hidden.bs.modal', function () {
            revertirSelectRol();
        });
    });
</script>
@endpush
