@extends('admin.main')
@section('title', 'Roles y Permisos')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>{{ Session::get('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-shield"></i> Roles y Permisos de Agentes</h4>
        </div>

        @if($agents->isEmpty())
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> No hay agentes registrados en tu empresa.
                <a href="{{ route('admin.users.create') }}">Crear un agente</a>
            </div>
        @else
            {{-- Tab navigation --}}
            <ul class="nav nav-tabs mb-3" id="agentTabs" role="tablist">
                @foreach($agents as $i => $agent)
                    <li class="nav-item">
                        <a class="nav-link {{ $i === 0 ? 'active' : '' }}"
                           id="tab-{{ $agent->id }}"
                           data-toggle="tab"
                           href="#agent-{{ $agent->id }}"
                           role="tab">
                            <i class="fa fa-user"></i> {{ $agent->name }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach($agents as $i => $agent)
                    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                         id="agent-{{ $agent->id }}"
                         role="tabpanel">

                        <form action="{{ route('admin.roles.update', $agent->id) }}" method="POST">
                            @csrf
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-user"></i> {{ $agent->name }}
                                        <small class="text-muted">— {{ $agent->email }}</small>
                                    </span>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success mr-1"
                                                onclick="toggleAll('{{ $agent->id }}', true)">
                                            <i class="fa fa-check-square-o"></i> Activar todos
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger mr-2"
                                                onclick="toggleAll('{{ $agent->id }}', false)">
                                            <i class="fa fa-square-o"></i> Desactivar todos
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fa fa-save"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($modules as $module => $config)
                                            @php
                                                $perms = $agent->modulePermissions?->permissions ?? [];
                                            @endphp
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-header py-2" style="background:#f8f9fa;">
                                                        <strong>
                                                            <i class="fa {{ $config['icon'] }}"></i>
                                                            {{ $config['label'] }}
                                                        </strong>
                                                    </div>
                                                    <div class="card-body py-2">
                                                        @foreach($config['actions'] as $action => $actionLabel)
                                                            @php
                                                                $checked = (bool) ($perms[$module][$action] ?? false);
                                                                $inputName = "permissions[{$module}][{$action}]";
                                                                $inputId = "perm_{$agent->id}_{$module}_{$action}";
                                                            @endphp
                                                            <div class="custom-control custom-switch mb-1">
                                                                <input type="checkbox"
                                                                       class="custom-control-input agent-perm-{{ $agent->id }}"
                                                                       id="{{ $inputId }}"
                                                                       name="{{ $inputName }}"
                                                                       value="1"
                                                                       {{ $checked ? 'checked' : '' }}>
                                                                <label class="custom-control-label" for="{{ $inputId }}">
                                                                    {{ $actionLabel }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Guardar permisos de {{ $agent->name }}
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function toggleAll(agentId, state) {
            document.querySelectorAll('.agent-perm-' + agentId).forEach(function(cb) {
                cb.checked = state;
            });
        }
    </script>
@endsection
