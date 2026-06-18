{{-- resources/views/admin/login.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SICET</title>
    
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 50%;
            display: inline-block;
        }
        
        .login-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
        }
        
        .login-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .form-floating > .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 1rem 0.75rem;
            height: auto;
            transition: all 0.3s;
        }
        
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.1);
        }
        
        .form-floating > label {
            padding: 1rem 0.75rem;
            color: #6c757d;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            z-index: 10;
        }
        
        .input-icon .form-control {
            padding-left: 2.8rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #667eea;
            z-index: 10;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            width: 100%;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert-custom {
            border-radius: 12px;
            border-left: 4px solid #dc3545;
            background: #fff5f5;
            color: #dc3545;
            padding: 0.8rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .alert-custom i {
            font-size: 1.2rem;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .spinner {
            display: inline-block;
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .forgot-link {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            
            {{-- Header --}}
            <div class="login-header">
                <i class="bi bi-shield-lock"></i>
                <h3>SICET</h3>
                <p>Sistema de Control de Equipos</p>
            </div>

            {{-- Body --}}
            <div class="login-body">
                
                {{-- Alertas de error --}}
                @if ($errors->any())
                    <div class="alert-custom">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                {{-- Alerta de éxito (para cuando cambian contraseña) --}}
                @if(session('success'))
                    <div class="alert alert-success alert-custom" style="border-left-color: #28a745; background: #d4edda; color: #155724;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Formulario --}}
                <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf

                    {{-- Número de Empleado (SOLO NÚMEROS) --}}
                    <div class="input-icon mb-3">
                        <i class="bi bi-person-badge"></i>
                        <input type="text"
                               name="email"  {{-- El controlador espera 'email' --}}
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="Número de Empleado"
                               autocomplete="off"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               required>
                    </div>

                    {{-- Contraseña --}}
                    <div class="input-icon mb-3">
                        <i class="bi bi-key"></i>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Contraseña"
                               required>
                        <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                    </div>

                    {{-- Botón de envío --}}
                    <button type="submit" class="btn-login" id="submitBtn">
                        <span class="button-text">Ingresar</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>

                    {{-- Enlace "Olvidé mi contraseña" --}}
                    <div class="forgot-link">
                        <a href="{{ route('password.request') }}">
                            <i class="bi bi-question-circle me-1"></i>
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    {{-- Enlaces --}}
                    <div class="footer-links">
                        <p class="mb-0">
                            <i class="bi bi-info-circle"></i>
                            Usa tu número de empleado y contraseña
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Scripts personalizados --}}
    <script>
        // Toggle contraseña
        document.getElementById('togglePassword').addEventListener('click', function () {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Spinner al enviar formulario
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = submitBtn.querySelector('.button-text');
            const spinner = submitBtn.querySelector('.spinner');
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Ingresando...';
            spinner.style.display = 'inline-block';
        });
    </script>
</body>
</html>