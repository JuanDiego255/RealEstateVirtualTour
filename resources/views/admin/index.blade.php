@extends('admin.main')

@section('title', 'Dashboard')

@section('content')
    @if ($message = Session::has('success'))
        <div class="alert-dismiss">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('success') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="single-report mb-xs-30">
                <div class="s-report-inner pr--20 pt--30 mb-3">
                    <div class="icon"><a class="text-white" href="{{ route('sectors') }}"><i class="fa fa-th-large"></i></a></div>
                    <div class="s-report-title d-flex justify-content-between">
                        <h3 class="header-title mb-0">Sectores</h3>
                    </div>
                    <div class="d-flex justify-content-between pb-2">
                        <h2>{{ $sectors->count() }}</h2>
                    </div>
                </div>
                <canvas height="50"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="single-report mb-xs-30">
                <div class="s-report-inner pr--20 pt--30 mb-3">
                    <div class="icon"><a class="text-white" href="{{ route('categories') }}"><i class="fa fa-folder-open"></i></a></div>
                    <div class="s-report-title d-flex justify-content-between">
                        <h3 class="header-title mb-0">Categorías</h3>
                    </div>
                    <div class="d-flex justify-content-between pb-2">
                        <h2>{{ $categories->count() }}</h2>
                    </div>
                </div>
                <canvas height="50"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="single-report mb-xs-30">
                <div class="s-report-inner pr--20 pt--30 mb-3">
                    <div class="icon"><a class="text-white" href="{{ route('property') }}"><i class="fa fa-building"></i></a></div>
                    <div class="s-report-title d-flex justify-content-between">
                        <h3 class="header-title mb-0">Propiedades</h3>
                    </div>
                    <div class="d-flex justify-content-between pb-2">
                        <h2>{{ $properties->count() }}</h2>
                    </div>
                </div>
                <canvas height="50"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="single-report mb-xs-30">
                <div class="s-report-inner pr--20 pt--30 mb-3">
                    <div class="icon"><a class="text-white" href="{{ route('vehicles') }}"><i class="fa fa-car"></i></a></div>
                    <div class="s-report-title d-flex justify-content-between">
                        <h3 class="header-title mb-0">Vehículos</h3>
                    </div>
                    <div class="d-flex justify-content-between pb-2">
                        <h2>{{ $vehicles->count() }}</h2>
                    </div>
                </div>
                <canvas height="50"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="single-report mb-xs-30">
                <div class="s-report-inner pr--20 pt--30 mb-3">
                    <div class="icon"><i class="fa fa-map"></i></div>
                    <div class="s-report-title d-flex justify-content-between">
                        <h3 class="header-title mb-0">Escenas</h3>
                    </div>
                    <div class="d-flex justify-content-between pb-2">
                        <h2> {{ $scenes->count() }} </h2>
                    </div>
                </div>
                <canvas height="50"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="single-report mb-xs-30">
                <div class="s-report-inner pr--20 pt--30 mb-3">
                    <div class="icon"><i class="fa fa-map-marker"></i></div>
                    <div class="s-report-title d-flex justify-content-between">
                        <h3 class="header-title mb-0">Puntos de acceso</h3>
                    </div>
                    <div class="d-flex justify-content-between pb-2">
                        <h2>{{ $hotspots->count() }}</h2>
                    </div>
                </div>
                <canvas height="50"></canvas>
            </div>
        </div>
    </div>
@endsection
