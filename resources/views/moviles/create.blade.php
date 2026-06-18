@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h2 class="mb-4">➕ Registrar Dispositivo Móvil</h2>

    {{-- Solo Admin puede ver el formulario --}}
    @if(auth()->user()->role !== 'admin')
        <div class="alert alert-danger">
            No tienes permisos para registrar dispositivos.
        </div>
    @else

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('moviles.store') }}" method="POST">
                @csrf

                <div class="row g-3">

                    {{-- Marca --}}
                    <div class="col-md-4">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca"
                               class="form-control"
                               value="{{ old('marca') }}"
                               required>
                    </div>

                    {{-- Modelo --}}
                    <div class="col-md-4">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo"
                               class="form-control"
                               value="{{ old('modelo') }}">
                    </div>

                    {{-- IMEI --}}
                    <div class="col-md-4">
                        <label class="form-label">IMEI</label>
                        <input type="text" name="imei"
                               class="form-control"
                               value="{{ old('imei') }}"
                               required>
                    </div>

                    {{-- Número SIM --}}
                    <div class="col-md-4">
                        <label class="form-label">Número de SIM</label>
                        <input type="text" name="numero_sim"
                               class="form-control"
                               value="{{ old('numero_sim') }}">
                    </div>

                    {{-- Número de teléfono --}}
                    <div class="col-md-4">
                        <label class="form-label">Número de Teléfono</label>
                        <input type="text" name="numero_telefono"
                               class="form-control"
                               value="{{ old('numero_telefono') }}">
                    </div>

                    {{-- Características --}}
                    <div class="col-md-12">
                        <label class="form-label">Características</label>
                        <textarea name="caracteristicas"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Detalles del dispositivo">{{ old('caracteristicas') }}</textarea>
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="">Seleccione</option>
                            <option value="nuevo" {{ old('estado') == 'nuevo' ? 'selected' : '' }}>Nuevo</option>
                            <option value="usado" {{ old('estado') == 'usado' ? 'selected' : '' }}>Usado</option>
                            <option value="dado de baja" {{ old('estado') == 'dado de baja' ? 'selected' : '' }}>Dado de baja</option>
                        </select>
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-success">💾 Guardar</button>
                    <a href="{{ route('moviles.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>

        </div>
    </div>

    @endif

</div>
@endsection