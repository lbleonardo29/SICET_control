<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - SICET</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
        }
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        .reset-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .reset-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 50%;
        }
        .reset-body {
            padding: 2rem;
        }
        .btn-reset {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <i class="bi bi-lock"></i>
                <h3>Nueva Contraseña</h3>
                <p>Ingresa tu nueva contraseña</p>
            </div>

            <div class="reset-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>
                            Nueva Contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               class="form-control form-control-lg"
                               required>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>
                            Confirmar Contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control form-control-lg"
                               required>
                    </div>

                    <button type="submit" class="btn-reset" id="submitBtn">
                        <span class="button-text">
                            <i class="bi bi-check-circle me-2"></i>
                            Cambiar Contraseña
                        </span>
                        <span class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>
                            Volver al inicio de sesión
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('submitBtn')?.addEventListener('click', function(e) {
            const btn = this;
            const text = btn.querySelector('.button-text');
            const spinner = btn.querySelector('.spinner-border');
            btn.disabled = true;
            text.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Cambiando...';
            spinner.style.display = 'inline-block';
            btn.closest('form').submit();
        });
    </script>
</body>
</html>