<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SICET — @yield('page-title', 'Control de Equipos')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sicet.ico') }}">
    {{-- Bootstrap 5 (para vistas de contenido que usan clases Bootstrap) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- SICET design system (sobreescribe sidebar, header y background) --}}
    <link rel="stylesheet" href="{{ asset('css/sicet-app.css') }}">
    @stack('styles')
</head>
<body>

{{-- ============ SIDEBAR ============ --}}
<aside class="s-sidebar" id="s-sidebar">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="s-brand">
        <div class="s-brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </div>
        <div class="s-brand-text">
            <span class="s-brand-title">SICET</span>
            <span class="s-brand-subtitle">Control de Equipos</span>
        </div>
    </a>

    {{-- Nav --}}
    <nav class="s-nav">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="s-nav-row {{ Route::is('dashboard') ? 'active' : '' }}">
            <svg class="s-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span class="s-nav-text">Dashboard</span>
        </a>

        <div class="s-nav-label">Computadoras</div>

        {{-- Computadoras --}}
        @php $compActive = Route::is('asignaciones.dashboard') || Route::is('asignaciones.*') || Route::is('equipos.*'); @endphp
        <div class="s-nav-row expandable {{ $compActive ? 'expanded' : '' }}" onclick="sToggle('nav-comp', this)">
            <svg class="s-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            <span class="s-nav-text">Computadoras</span>
            <svg class="s-nav-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
        <div class="s-sub-nav {{ $compActive ? 'open' : '' }}" id="nav-comp">
            <a href="{{ route('asignaciones.dashboard') }}"
               class="s-sub-link {{ Route::is('asignaciones.dashboard') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Asignaciones
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('equipos.create') }}"
               class="s-sub-link {{ Route::is('equipos.create') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Registrar equipo
            </a>
            @endif
            <a href="{{ route('equipos.index') }}"
               class="s-sub-link {{ Route::is('equipos.index') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Todos los equipos
            </a>
            <a href="{{ route('equipos.disponibles') }}"
               class="s-sub-link {{ Route::is('equipos.disponibles') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Disponibles
            </a>
        </div>

        <div class="s-nav-label">Dispositivos</div>

        {{-- Dispositivos --}}
        @php $dispActive = Route::is('moviles.*') || Route::is('asignaciones.moviles.*'); @endphp
        <div class="s-nav-row expandable {{ $dispActive ? 'expanded' : '' }}" onclick="sToggle('nav-disp', this)">
            <svg class="s-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                <line x1="12" y1="18" x2="12.01" y2="18"/>
            </svg>
            <span class="s-nav-text">Dispositivos</span>
            <svg class="s-nav-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
        <div class="s-sub-nav {{ $dispActive ? 'open' : '' }}" id="nav-disp">
            <a href="{{ route('asignaciones.moviles.dashboard') }}"
               class="s-sub-link {{ Route::is('asignaciones.moviles.dashboard') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Asignaciones
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('moviles.create') }}"
               class="s-sub-link {{ Route::is('moviles.create') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Registrar dispositivo
            </a>
            @endif
            <a href="{{ route('moviles.index') }}"
               class="s-sub-link {{ Route::is('moviles.index') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Todos
            </a>
            <a href="{{ route('moviles.disponibles') }}"
               class="s-sub-link {{ Route::is('moviles.disponibles') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Disponibles
            </a>
        </div>

        <div class="s-nav-label">Personal</div>

        {{-- Empleados --}}
        @php $empActive = Route::is('empleados.*'); @endphp
        <div class="s-nav-row expandable {{ $empActive ? 'expanded' : '' }}" onclick="sToggle('nav-emp', this)">
            <svg class="s-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="s-nav-text">Empleados</span>
            <svg class="s-nav-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
        <div class="s-sub-nav {{ $empActive ? 'open' : '' }}" id="nav-emp">
            <a href="{{ route('empleados.index') }}"
               class="s-sub-link {{ Route::is('empleados.index') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Todos los empleados
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('empleados.create') }}"
               class="s-sub-link {{ Route::is('empleados.create') ? 'active' : '' }}">
                <span class="s-sub-dot"></span> Registrar empleado
            </a>
            @endif
        </div>

        {{-- Usuarios (solo admin) --}}
        @if(Auth::user()->role === 'admin')
        <div class="s-nav-label">Sistema</div>
        <a href="{{ route('usuarios.index') }}"
           class="s-nav-row {{ Route::is('usuarios.*') ? 'active' : '' }}">
            <svg class="s-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="s-nav-text">Usuarios</span>
        </a>
        @endif

        {{-- Reportes --}}
        @if(in_array(Auth::user()->role, ['admin', 'seguridad']))
        <div class="s-nav-label">Reportes</div>
        @php
            $reportesRoute = Auth::user()->role === 'admin'
                ? route('reportes.index')
                : route('reportes.create');
        @endphp
        <a href="{{ $reportesRoute }}"
           class="s-nav-row {{ Route::is('reportes.*') ? 'active' : '' }}">
            <svg class="s-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <span class="s-nav-text">Reportes</span>
        </a>
        @endif

    </nav>

    {{-- Logout --}}
    <div class="s-logout">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="s-logout-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>

</aside>

{{-- ============ MAIN ============ --}}
<div class="s-main">

    {{-- Header --}}
    <header class="s-header">
        <div class="s-header-left">
            <h1 class="s-page-title">@yield('page-title', 'Dashboard')</h1>
            @hasSection('page-subtitle')
                <p class="s-page-subtitle">@yield('page-subtitle')</p>
            @endif
        </div>

        <div class="s-header-right">
            <span class="s-date-chip">
                {{ now()->locale('es')->translatedFormat('j \d\e F, Y') }}
            </span>

            <button class="s-notif-btn" title="Notificaciones" type="button">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="s-notif-dot"></span>
            </button>

            <a href="{{ route('perfil.index') }}" class="s-user-chip">
                <div class="s-avatar">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}"
                             alt="{{ Auth::user()->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="s-user-info">
                    <span class="s-user-name">{{ Auth::user()->name }}</span>
                    <span class="s-user-role">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </a>
        </div>
    </header>

    {{-- Content --}}
    <main class="s-content">

        @if(session('success'))
            <div class="s-alert s-alert-success" id="flash-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
                <button class="s-alert-close" onclick="this.closest('.s-alert').remove()">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="s-alert s-alert-error" id="flash-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                {{ session('error') }}
                <button class="s-alert-close" onclick="this.closest('.s-alert').remove()">✕</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="s-alert s-alert-warning" id="flash-warning">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                {{ session('warning') }}
                <button class="s-alert-close" onclick="this.closest('.s-alert').remove()">✕</button>
            </div>
        @endif

        @yield('content')

    </main>

    <footer class="s-footer">
         {{ date('Y') }} SICET — Sistema de Control de Equipos · Fruitex de México
    </footer>

</div>

<script>
function sToggle(navId, triggerEl) {
    var sub = document.getElementById(navId);
    var isOpen = sub.classList.contains('open');
    sub.classList.toggle('open', !isOpen);
    triggerEl.classList.toggle('expanded', !isOpen);
}

setTimeout(function () {
    ['flash-success', 'flash-error', 'flash-warning'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.remove();
    });
}, 5000);
</script>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>
