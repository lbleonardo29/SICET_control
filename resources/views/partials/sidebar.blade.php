<div class="sidebar">

    {{-- LOGO --}}
    <div class="text-white text-center py-4 fw-bold fs-5 border-bottom">
         SICET
    </div>

    {{-- DASHBOARD --}}
    <a href="{{ route('dashboard') }}"
       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>
        <span class="text">Dashboard</span>
    </a>

    {{-- ================= ADMIN ================= --}}
    @if(auth()->user()->role === 'admin')

        <div class="sidebar-section text-uppercase text-secondary small mt-3 px-3">
            Administración
        </div>

        {{-- ===== COMPUTADORAS ===== --}}
        <a class="sidebar-link d-flex justify-content-between align-items-center"
           data-bs-toggle="collapse"
           href="#menuComputadoras"
           aria-expanded="{{ request()->routeIs('equipos.*') || request()->routeIs('asignaciones.*') ? 'true' : 'false' }}">
            <span>
                <i class="bi bi-pc-display me-2"></i>
                <span class="text">Computadoras</span>
            </span>
            <span class="arrow">▼</span>
        </a>

        <div class="collapse {{ request()->routeIs('equipos.*') || request()->routeIs('asignaciones.*') ? 'show' : '' }}" id="menuComputadoras">
            <a href="{{ route('asignaciones.dashboard') }}" class="sidebar-sublink {{ request()->routeIs('asignaciones.dashboard') ? 'active' : '' }}">
                <i class="bi bi-list-check me-2"></i>
                <span class="text">Asignaciones</span>
            </a>
            <a href="{{ route('equipos.create') }}" class="sidebar-sublink {{ request()->routeIs('equipos.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle me-2"></i>
                <span class="text">Registrar Computadora</span>
            </a>
            <a href="{{ route('equipos.index') }}" class="sidebar-sublink {{ request()->routeIs('equipos.index') ? 'active' : '' }}">
                <i class="bi bi-list-ul me-2"></i>
                <span class="text">Todas las Computadoras</span>
            </a>
            <a href="{{ route('equipos.disponibles') }}" class="sidebar-sublink {{ request()->routeIs('equipos.disponibles') ? 'active' : '' }}">
                <i class="bi bi-check-circle me-2"></i>
                <span class="text">Disponibles</span>
            </a>
        </div>

        {{-- ===== MOVILES ===== --}}
        <a class="sidebar-link d-flex justify-content-between align-items-center mt-2"
           data-bs-toggle="collapse"
           href="#menuMoviles"
           aria-expanded="{{ request()->routeIs('moviles.*') || request()->routeIs('asignaciones.moviles.*') ? 'true' : 'false' }}">
            <span>
                <i class="bi bi-phone me-2"></i>
                <span class="text">Dispositivos</span>
            </span>
            <span class="arrow">▼</span>
        </a>

        <div class="collapse {{ request()->routeIs('moviles.*') || request()->routeIs('asignaciones.moviles.*') ? 'show' : '' }}" id="menuMoviles">
            <a href="{{ route('asignaciones.moviles.dashboard') }}" class="sidebar-sublink {{ request()->routeIs('asignaciones.moviles.dashboard') ? 'active' : '' }}">
                <i class="bi bi-list-check me-2"></i>
                <span class="text">Asignaciones</span>
            </a>
            <a href="{{ route('moviles.create') }}" class="sidebar-sublink {{ request()->routeIs('moviles.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle me-2"></i>
                <span class="text">Registrar Dispositivo</span>
            </a>
            <a href="{{ route('moviles.index') }}" class="sidebar-sublink {{ request()->routeIs('moviles.index') ? 'active' : '' }}">
                <i class="bi bi-list-ul me-2"></i>
                <span class="text">Todos los Dispositivos</span>
            </a>
            <a href="{{ route('moviles.disponibles') }}" class="sidebar-sublink {{ request()->routeIs('moviles.disponibles') ? 'active' : '' }}">
                <i class="bi bi-check-circle me-2"></i>
                <span class="text">Disponibles</span>
            </a>
        </div>

        {{-- ===== EMPLEADOS ===== --}}
        <a class="sidebar-link d-flex justify-content-between align-items-center mt-2"
           data-bs-toggle="collapse"
           href="#menuEmpleados"
           aria-expanded="{{ request()->routeIs('empleados.*') ? 'true' : 'false' }}">
            <span>
                <i class="bi bi-people me-2"></i>
                <span class="text">Empleados</span>
            </span>
            <span class="arrow">▼</span>
        </a>

        <div class="collapse {{ request()->routeIs('empleados.*') ? 'show' : '' }}" id="menuEmpleados">
            <a href="{{ route('empleados.index') }}" class="sidebar-sublink {{ request()->routeIs('empleados.index') ? 'active' : '' }}">
                <i class="bi bi-list-ul me-2"></i>
                <span class="text">Todos los Empleados</span>
            </a>
        </div>

        {{-- ===== REPORTES (solo admin) ===== --}}
        <a href="{{ route('reportes.index') }}" 
           class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <i class="bi bi-file-text me-2"></i>
            <span class="text">Reportes de Seguridad</span>
        </a>

    @endif

    {{-- ================= SEGURIDAD ================= --}}
    @if(auth()->user()->role === 'seguridad')
        <div class="sidebar-section text-uppercase text-secondary small mt-3 px-3">
            Seguridad
        </div>

        <a href="{{ route('reportes.create') }}" class="sidebar-link">
            <i class="bi bi-clipboard-data me-2"></i>
            <span class="text">Registrar Movimiento</span>
        </a>
    @endif

    {{-- ================= USUARIO ================= --}}
    @if(auth()->user()->role === 'usuario')
        <div class="sidebar-section text-uppercase text-secondary small mt-3 px-3">
            Mis Equipos
        </div>

        <a href="{{ route('dashboard') }}" class="sidebar-link">
            <i class="bi bi-pc-display me-2"></i>
            <span class="text">Ver mis asignaciones</span>
        </a>
    @endif

    <hr class="text-secondary mx-3">

    {{-- LOGOUT --}}
    <div class="px-3 mb-3">
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i>
                <span class="text">Cerrar sesión</span>
            </button>
        </form>
    </div>

</div>

<script>
    // Manejar colapso del sidebar en móvil
    document.addEventListener('DOMContentLoaded', function() {
        // Prevenir que los enlaces colapsen cuando se hace clic en ellos
        const submenuLinks = document.querySelectorAll('.sidebar-sublink');
        submenuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
</script>