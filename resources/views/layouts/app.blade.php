<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SICET - {{ $title ?? 'Sistema de Control de Computadoras' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sicet.ico') }}">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 240px;
            height: 100vh;
            background: linear-gradient(180deg, #212529 0%, #1a1e21 100%);
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: width 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar.collapsed { width: 70px; }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover {
            background-color: #343a40;
            color: #fff;
            border-left-color: #0d6efd;
        }
        .sidebar a.active {
            background-color: #0d6efd;
            color: #fff;
            border-left-color: #fff;
        }
        .sidebar-section {
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.7rem;
            padding: 16px 16px 8px;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .sidebar-sublink { padding-left: 32px !important; }
        .arrow { 
            font-size: 0.7rem; 
            margin-left: auto;
            transition: transform 0.2s ease;
        }
        .sidebar a[aria-expanded="true"] .arrow {
            transform: rotate(90deg);
        }

        .sidebar.collapsed a { justify-content: center; padding: 12px; }
        .sidebar.collapsed .text { 
            opacity: 0; 
            width: 0; 
            overflow: hidden; 
            display: none;
        }
        .sidebar.collapsed .sidebar-section { display: none; }
        .sidebar.collapsed .arrow { display: none; }

        /* ===== HEADER / TOPBAR ===== */
        .header {
            height: 70px;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0 1.5rem;
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: left 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            
        }
        .sidebar.collapsed ~ .main-wrapper .header { left: 70px; }

        /* ===== MAIN WRAPPER ===== */
        .main-wrapper {
            margin-left: 240px;
            padding-top: 70px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        .sidebar.collapsed ~ .main-wrapper { margin-left: 70px; }
        main.content {
            padding: 1.5rem;
            min-height: calc(100vh - 70px);
            overflow-y: auto;
        }

        /* ===== MODO OSCURO ===== */
        body.dark-mode {
            background-color: #1a1a2e;
            color: #e0e0e0;
        }

        body.dark-mode .header { 
            background: #16213e; 
            color: #fff; 
            border-bottom-color: #0f3460;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        body.dark-mode .sidebar { 
            background: linear-gradient(180deg, #0f3460 0%, #16213e 100%);
        }

        body.dark-mode .sidebar a { 
            color: #a5b4cb; 
        }

        body.dark-mode .sidebar a:hover { 
            background-color: #1e3a6b; 
            color: #fff;
        }

        body.dark-mode .sidebar a.active { 
            background-color: #0d6efd; 
            color: #fff;
        }

        /* ===== TEXTOS ===== */
        body.dark-mode .text-dark {
            color: #e0e0e0 !important;
        }

        body.dark-mode .text-primary {
            color: #6ea8fe !important;
        }

        body.dark-mode .text-success {
            color: #75b798 !important;
        }

        body.dark-mode .text-danger {
            color: #ea868f !important;
        }

        body.dark-mode .text-warning {
            color: #ffda6a !important;
        }

        body.dark-mode .text-info {
            color: #6edff6 !important;
        }

        body.dark-mode .text-muted { 
            color: #a5b4cb !important; 
        }

        /* ===== TARJETAS ===== */
        body.dark-mode .card { 
            background-color: #16213e; 
            color: #e0e0e0; 
            border-color: #0f3460;
        }

        body.dark-mode .card-header {
            background-color: #1e2a47;
            color: #fff;
            border-bottom-color: #0f3460;
        }

        body.dark-mode .card-footer {
            background-color: #1e2a47;
            border-top-color: #0f3460;
        }

        /* ===== TABLAS ===== */
        body.dark-mode .table { 
            color: #e0e0e0; 
        }

        body.dark-mode .table thead th {
            background-color: #0f3460;
            color: #fff;
            border-color: #1e3a6b;
        }

        body.dark-mode .table tbody tr:hover {
            background-color: #1e2a47;
        }

        body.dark-mode .table td, 
        body.dark-mode .table th {
            border-color: #0f3460;
        }

        /* ===== FORMULARIOS ===== */
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #1e2a47;
            border-color: #0f3460;
            color: #e0e0e0;
        }

        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background-color: #2a3a5a;
            border-color: #6ea8fe;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(110, 168, 254, 0.25);
        }

        body.dark-mode .form-control:disabled,
        body.dark-mode .form-select:disabled {
            background-color: #0f1a2b;
            color: #6c757d;
        }

        body.dark-mode .input-group-text {
            background-color: #0f3460;
            border-color: #0f3460;
            color: #e0e0e0;
        }

        /* ===== BOTONES ===== */
        body.dark-mode .btn-outline-secondary {
            color: #a5b4cb;
            border-color: #0f3460;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #0f3460;
            color: #fff;
        }

        body.dark-mode .btn-outline-dark {
            color: #e0e0e0;
            border-color: #0f3460;
        }

        body.dark-mode .btn-outline-dark:hover {
            background-color: #0f3460;
            color: #fff;
        }

        body.dark-mode .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        body.dark-mode .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        /* ===== DROPDOWNS ===== */
        body.dark-mode .dropdown-menu {
            background-color: #16213e;
            border-color: #0f3460;
        }

        body.dark-mode .dropdown-item {
            color: #e0e0e0;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: #0f3460;
            color: #fff;
        }

        body.dark-mode .dropdown-divider {
            border-top-color: #0f3460;
        }

        /* ===== ALERTAS ===== */
        body.dark-mode .alert-success {
            background-color: #1e4a2e;
            color: #d4edda;
            border-color: #2e6b3e;
        }

        body.dark-mode .alert-danger {
            background-color: #4a1e2e;
            color: #f8d7da;
            border-color: #6b2e3e;
        }

        body.dark-mode .alert-warning {
            background-color: #4a3e1e;
            color: #fff3cd;
            border-color: #6b4e2e;
        }

        body.dark-mode .alert-info {
            background-color: #1e3a4a;
            color: #d1ecf1;
            border-color: #2e4e6b;
        }

        /* ===== MODALES ===== */
        body.dark-mode .modal-content {
            background-color: #16213e;
            color: #e0e0e0;
            border-color: #0f3460;
        }

        body.dark-mode .modal-header {
            border-bottom-color: #0f3460;
        }

        body.dark-mode .modal-footer {
            border-top-color: #0f3460;
        }

        /* ===== FOOTER ===== */
        body.dark-mode .footer,
        body.dark-mode .bg-white {
            background-color: #16213e !important;
            color: #a5b4cb;
            border-top-color: #0f3460 !important;
        }

        /* ===== BADGES ===== */
        body.dark-mode .badge.bg-success {
            background-color: #198754 !important;
        }

        body.dark-mode .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        body.dark-mode .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        body.dark-mode .badge.bg-info {
            background-color: #0dcaf0 !important;
            color: #000 !important;
        }

        body.dark-mode .badge.bg-primary {
            background-color: #0d6efd !important;
        }

        body.dark-mode .badge.bg-secondary {
            background-color: #6c757d !important;
        }

        /* ===== PAGINACIÓN ===== */
        .pagination svg { width: 16px !important; height: 16px !important; }
        .pagination .page-link { 
            border-radius: 8px; 
            margin: 0 2px;
            transition: all 0.2s ease;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        body.dark-mode .pagination .page-link {
            background-color: #1e2a47;
            border-color: #0f3460;
            color: #e0e0e0;
        }

        body.dark-mode .pagination .page-link:hover {
            background-color: #0f3460;
            color: #fff;
        }

        body.dark-mode .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        /* ===== SCROLLBAR PERSONALIZADO ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        body.dark-mode ::-webkit-scrollbar-track {
            background: #1e2a47;
        }
        body.dark-mode ::-webkit-scrollbar-thumb {
            background: #0f3460;
        }
        body.dark-mode ::-webkit-scrollbar-thumb:hover {
            background: #1e3a6b;
        }
        
        /* ===== ANIMACIONES ===== */
        .fade-enter {
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
@include('partials.sidebar')

<div class="main-wrapper">

    {{-- Header --}}
    @include('partials.header')

    {{-- Breadcrumb dinámico (opcional) --}}
    @if(isset($breadcrumbs))
    <nav aria-label="breadcrumb" class="px-4 py-2 bg-light border-bottom">
        <ol class="breadcrumb mb-0">
            @foreach($breadcrumbs as $breadcrumb)
                @if($loop->last)
                    <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
    @endif

    {{-- Contenido principal --}}
    <main class="content fade-enter">

        {{-- Alertas --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="text-center text-muted py-3 border-top bg-white">
        <small>
            <i class="bi bi-c-circle me-1"></i>
            {{ date('Y') }} SICET - Sistema de Control de Computadoras. v1.0
        </small>
    </footer>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const toggleSidebarBtn = document.getElementById('toggleSidebar');
    const toggleDarkBtn = document.getElementById('toggleDark');
    const darkIcon = toggleDarkBtn?.querySelector('i');

    // ===== RESTAURAR ESTADOS =====
    // Modo oscuro
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        if (darkIcon) {
            darkIcon.classList.remove('bi-moon');
            darkIcon.classList.add('bi-sun');
        }
    }

    // Sidebar colapsado
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        sidebar.classList.add('collapsed');
    }

    // ===== TOGGLE SIDEBAR =====
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
        });
    }

    // ===== TOGGLE DARK MODE =====
    if (toggleDarkBtn && darkIcon) {
        toggleDarkBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            
            if (isDark) {
                darkIcon.classList.remove('bi-moon');
                darkIcon.classList.add('bi-sun');
            } else {
                darkIcon.classList.remove('bi-sun');
                darkIcon.classList.add('bi-moon');
            }
            
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
        });
    }

    // ===== MANEJO DE SUBMENÚS =====
    document.querySelectorAll('.sidebar a[data-bs-toggle="collapse"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (sidebar.classList.contains('collapsed')) {
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>

@stack('scripts')

</body>
</html>