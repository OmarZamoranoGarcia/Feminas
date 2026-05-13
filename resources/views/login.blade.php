@extends('app')

@section('title', 'Iniciar Sesión - DevMart')

@section('content')
<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-0">Bienvenido de nuevo</h3>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted text-center mb-4">Ingresa tus credenciales para acceder</p>
                    
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" placeholder="nombre@ejemplo.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('home') }}" class="text-decoration-none">← Volver al inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Aviso: Por el momento no hay base de datos configurada. Esta funcionalidad estará disponible pronto.');
    });
</script>
@endpush