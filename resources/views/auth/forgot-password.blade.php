<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SICET</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert-success {
            border-radius: 12px;
            background: #d4edda;
            color: #155724;
            border: none;
        }
        .alert-danger {
            border-radius: 12px;
            background: #f8d7da;
            color: #721c24;
            border: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <i class="bi bi-envelope-paper"></i>
                <h3>Recuperar Contraseña</h3>
                <p>Te enviaremos un enlace a tu correo</p>
            </div>

            <div class="reset-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1"></i>
                            Correo electrónico
                        </label>
                        <input type="email"
                               name="correo"
                               class="form-control form-control-lg"
                               value="{{ old('correo') }}"
                               placeholder="tucorreo@empresa.com"
                               required>
                        <small class="text-muted">Ingresa el correo con el que estás registrado en SICET</small>
                    </div>

                    <button type="submit" class="btn-reset" id="submitBtn">
                        <span class="button-text">
                            <i class="bi bi-envelope me-2"></i>
                            Enviar enlace de recuperación
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
            text.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Enviando...';
            spinner.style.display = 'inline-block';
            btn.closest('form').submit();
        });
    </script>
</body>
</html>