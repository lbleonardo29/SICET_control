<nav class="layout-navbar navbar navbar-expand-xl navbar-light bg-white shadow-sm header" 
     style="padding-left: 5px !important; padding-right: 5px !important;">

    <div class="d-flex align-items-center w-100">

        {{-- BOTÓN SIDEBAR --}}
        <button id="toggleSidebar" class="btn btn-sm btn-outline-secondary me-3" title="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        {{-- BIENVENIDA --}}
        <div class="fw-semibold d-none d-sm-block">
            <i class="bi bi-hand-index-thumb me-1 text-primary"></i>
            Bienvenido,
            <span class="text-primary fw-bold">
                {{ auth()->user()->name }}
            </span>
        </div>

        {{-- BIENVENIDA MÓVIL --}}
        <div class="fw-semibold d-sm-none">
            <span class="text-primary fw-bold">
                {{ explode(' ', auth()->user()->name)[0] }}
            </span>
        </div>

        {{-- LADO DERECHO --}}
        <div class="d-flex align-items-center ms-auto gap-2 gap-sm-3">

            {{-- BOTÓN DARK MODE --}}
            <button id="toggleDark" class="btn btn-sm btn-outline-dark" title="Cambiar modo oscuro">
                <i class="bi bi-moon"></i>
            </button>

            {{-- USUARIO --}}
            <div class="dropdown">
                <a href="#"
                   class="d-flex align-items-center text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">

                    <img src="{{ auth()->user()->profile_photo
                        ? asset('storage/'.auth()->user()->profile_photo)
                        : asset('img/default-user.png') }}"
                        class="rounded-circle me-2 border border-2 border-primary"
                        width="38"
                        height="38"
                        alt="Foto de usuario"
                        style="object-fit: cover;">

                    <div class="d-none d-md-block">
                        <div class="fw-bold text-dark">
                            {{ auth()->user()->name }}
                        </div>
                        <small class="text-muted text-capitalize d-flex align-items-center">
                            <i class="bi bi-shield-{{ auth()->user()->role === 'admin' ? 'lock' : 'person' }} me-1"></i>
                            {{ auth()->user()->role }}
                        </small>
                    </div>

                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm py-2">

                    {{-- Perfil --}}
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('perfil.index') }}">
                            <i class="bi bi-person me-2 text-primary"></i>
                            Mi perfil
                        </a>
                    </li>

                    <li><hr class="dropdown-divider my-1"></li>

                    {{-- Dashboard --}}
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-2 text-success"></i>
                            Dashboard
                        </a>
                    </li>

                    <li><hr class="dropdown-divider my-1"></li>

                    {{-- Logout --}}
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Cerrar sesión
                            </button>
                        </form>
                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>

{{-- Bootstrap Icons (si no están en el layout) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

