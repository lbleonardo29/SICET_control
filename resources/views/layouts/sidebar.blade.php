{{-- ===== EMPLEADOS ===== --}}
<div class="px-3 text-uppercase text-secondary small mt-3">
    👥 Empleados
</div>

<a href="{{ route('empleados.index') }}"
   class="{{ request()->routeIs('empleados.*') ? 'active' : '' }}">
    📄 Todos los Empleados
</a>