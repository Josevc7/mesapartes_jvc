@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Dashboard - Sistema Mesa de Partes</h2>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Bienvenido al Sistema</h4>
                </div>
                <div class="card-body">
                    <p>Usuario: {{ auth()->user()->name }}</p>
                    <p>Email: {{ auth()->user()->email }}</p>
                    @if(auth()->user()->role)
                        <p>Rol: {{ auth()->user()->role->nombre }}</p>
                    @else
                        <p>Rol: Sin rol asignado</p>
                    @endif
                    
                    <div class="mt-4">
                        <h5>Accesos Rápidos:</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('ciudadano.registrar-expediente') }}" class="btn btn-primary btn-block">Nuevo Expediente</a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('seguimiento.form') }}" class="btn btn-info btn-block">Consultar Expediente</a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('mesa-partes.index') }}" class="btn btn-warning btn-block">Mesa de Partes</a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.usuarios') }}" class="btn btn-success btn-block">Administración</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection