@extends('admin.main')
@section('title', 'CRM - Leads')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button
                type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        {{-- Stats Cards --}}
        <div class="row mb-4">
            <div class="col-md-2 col-sm-6">
                <div class="card bg-primary text-white">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        <small>Total Leads</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card bg-info text-white">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $stats['new'] }}</h3>
                        <small>Nuevos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card bg-warning text-white">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $stats['active'] }}</h3>
                        <small>Activos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card bg-success text-white">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $stats['won'] }}</h3>
                        <small>Ganados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="card bg-danger text-white">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $stats['needs_follow_up'] }}</h3>
                        <small>Seguimiento</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <a href="{{ route('admin.crm.leads.pipeline') }}" class="card bg-dark text-white text-decoration-none">
                    <div class="card-body py-3 text-center">
                        <i class="fa fa-columns fa-2x"></i>
                        <small class="d-block mt-1">Pipeline</small>
                    </div>
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-users"></i> Gestión de Leads</h4>
            <div>
                <a href="{{ route('admin.crm.leads.follow-ups') }}" class="btn btn-warning mr-2">
                    <i class="fa fa-clock-o"></i> Seguimientos
                </a>
                <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Nuevo Lead
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.crm.leads.index') }}" class="row">
                    <div class="col-md-3 mb-2">
                        <input type="text" name="search" class="form-control" placeholder="Buscar nombre, email, teléfono..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="status" class="form-control">
                            <option value="">Todos los estados</option>
                            @foreach(\App\Lead::getStatuses() as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="source" class="form-control">
                            <option value="">Todas las fuentes</option>
                            @foreach(\App\Lead::getSources() as $key => $label)
                                <option value="{{ $key }}" {{ request('source') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="priority" class="form-control">
                            <option value="">Todas las prioridades</option>
                            @foreach(\App\Lead::getPriorities() as $key => $label)
                                <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="user_id" class="form-control">
                            <option value="">Todos los agentes</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('user_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-2">
                        <button type="submit" class="btn btn-secondary btn-block"><i class="fa fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Leads Table --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Fuente</th>
                            <th>Prioridad</th>
                            <th>Interés</th>
                            <th>Agente</th>
                            <th>Seguimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr class="{{ $lead->isOverdueForFollowUp() ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $lead->name }}</strong>
                                    @if($lead->property)
                                        <br><small class="text-muted"><i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 20) }}</small>
                                    @endif
                                    @if($lead->vehicle)
                                        <br><small class="text-muted"><i class="fa fa-car"></i> {{ Str::limit($lead->vehicle->name, 20) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->phone)
                                        <small><i class="fa fa-phone"></i> {{ $lead->phone }}</small><br>
                                    @endif
                                    @if($lead->email)
                                        <small><i class="fa fa-envelope"></i> {{ $lead->email }}</small>
                                    @endif
                                    @if($lead->whatsapp)
                                        <br><small class="text-success"><i class="fa fa-whatsapp"></i> {{ $lead->whatsapp }}</small>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                                <td><small>{{ $lead->source_label }}</small></td>
                                <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                                <td><small>{{ $lead->interest_type_label }}</small></td>
                                <td><small>{{ $lead->user->name ?? '-' }}</small></td>
                                <td>
                                    @if($lead->next_follow_up)
                                        @if($lead->isOverdueForFollowUp())
                                            <span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ $lead->next_follow_up->format('d/m/Y') }}</span>
                                        @elseif($lead->needsFollowUpToday())
                                            <span class="text-warning"><i class="fa fa-clock-o"></i> Hoy</span>
                                        @else
                                            <small>{{ $lead->next_follow_up->format('d/m/Y') }}</small>
                                        @endif
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-outline-info" title="Ver"><i class="fa fa-eye"></i></a>
                                        <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-outline-primary" title="Editar"><i class="fa fa-edit"></i></a>
                                        <a href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}" class="btn btn-outline-success" title="Programar Cita"><i class="fa fa-calendar-plus-o"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No hay leads registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $leads->links() }}
            </div>
        </div>
    </div>
@endsection
