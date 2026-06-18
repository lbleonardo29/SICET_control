@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">✏️ Editar Dispositivo Móvil</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('moviles.update', $movil) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Marca --}}
                <div class="mb-3">
                    <label class="form-label">Marca</label>
                    <input type="text"
                           name="marca"
                           class="form-control"
                           value="{{ old('marca', $movil->marca) }}"
                           required>
                </div>

                {{-- Modelo --}}
                <div class="mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text"
                           name="modelo"
                           class="form-control"
                           value="{{ old('modelo', $movil->modelo) }}">
                </div>

                {{-- IMEI --}}
                <div class="mb-3">
                    <label class="form-label">IMEI</label>
                    <input type="text"
                           name="imei"
                           class="form-control"
                           value="{{ old('imei', $movil->imei) }}"
                           required>
                </div>

                {{-- Número SIM --}}
                <div class="mb-3">
                    <label class="form-label">Número de SIM</label>
                    <input type="text"
                           name="numero_sim"
                           class="form-control"
                           value="{{ old('numero_sim', $movil->numero_sim) }}">
                </div>

                {{-- Número de Teléfono --}}
                <div class="mb-3">
                    <label class="form-label">Número de Teléfono</label>
                    <input type="text"
                           name="numero_telefono"
                           class="form-control"
                           value="{{ old('numero_telefono', $movil->numero_telefono) }}">
                </div>

                {{-- Características --}}
                <div class="mb-3">
                    <label class="form-label">Características</label>
                    <textarea name="caracteristicas"
                              class="form-control"
                              rows="3">{{ old('caracteristicas', $movil->caracteristicas) }}</textarea>
                </div>

                {{-- Estado --}}
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="">Seleccione</option>
                        <option value="nuevo" {{ old('estado', $movil->estado) == 'nuevo' ? 'selected' : '' }}>Nuevo</option>
                        <option value="usado" {{ old('estado', $movil->estado) == 'usado' ? 'selected' : '' }}>Usado</option>
                        <option value="dado de baja" {{ old('estado', $movil->estado) == 'dado de baja' ? 'selected' : '' }}>Dado de baja</option>
                    </select>
                </div>

                <button class="btn btn-primary">
                    💾 Guardar cambios
                </button>

                <a href="{{ route('moviles.index') }}" class="btn btn-secondary">
                    ↩️ Cancelar
                </a>
            </form>
        </div>
    </div>
</div>
@endsection