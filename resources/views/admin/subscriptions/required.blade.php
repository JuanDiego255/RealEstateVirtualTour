@extends('admin.main')
@section('title', 'Suscripción Requerida')
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card border-warning">
                    <div class="card-body text-center py-5">
                        <i class="fa fa-lock fa-4x text-warning mb-4"></i>
                        <h4>Suscripción Requerida</h4>
                        <p class="text-muted mb-4">
                            Necesitas una suscripción activa para acceder a esta funcionalidad.
                            Adquiere un paquete para comenzar.
                        </p>
                        <a href="{{ route('admin.subscriptions.my') }}" class="btn btn-primary btn-lg">
                            <i class="fa fa-credit-card"></i> Ver Paquetes Disponibles
                        </a>
                        <br>
                        <a href="{{ route('home') }}" class="btn btn-link mt-3">
                            <i class="fa fa-arrow-left"></i> Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
