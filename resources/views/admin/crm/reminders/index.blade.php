@extends('admin.main')
@section('title', 'Recordatorios')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        {{-- Stats Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <a href="{{ route('admin.crm.reminders.index', ['filter' => 'pending']) }}" class="card text-decoration-none {{ $filter == 'pending' ? 'bg-primary text-white' : '' }}">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $counts['pending'] }}</h3>
                        <small>Pendientes</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.crm.reminders.index', ['filter' => 'due']) }}" class="card text-decoration-none {{ $filter == 'due' ? 'bg-warning text-dark' : '' }}">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $counts['due'] }}</h3>
                        <small>Vencidos</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.crm.reminders.index', ['filter' => 'today']) }}" class="card text-decoration-none {{ $filter == 'today' ? 'bg-info text-white' : '' }}">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $counts['today'] }}</h3>
                        <small>Hoy</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.crm.reminders.index', ['filter' => 'overdue']) }}" class="card text-decoration-none {{ $filter == 'overdue' ? 'bg-danger text-white' : '' }}">
                    <div class="card-body py-3 text-center">
                        <h3 class="mb-0">{{ $counts['overdue'] }}</h3>
                        <small>Atrasados</small>
                    </div>
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-bell"></i> Recordatorios</h4>
            <a href="{{ route('admin.crm.reminders.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nuevo Recordatorio
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @forelse($reminders as $reminder)
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom {{ $reminder->isOverdue() ? 'bg-light' : '' }}">
                        <div class="mr-3">
                            <span class="badge badge-{{ $reminder->priority_color }} p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-bell fa-lg"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">
                                        {{ $reminder->title }}
                                        @if($reminder->is_recurring)
                                            <small class="badge badge-secondary"><i class="fa fa-refresh"></i> {{ $reminder->recurrence_type_label }}</small>
                                        @endif
                                    </h5>
                                    @if($reminder->description)
                                        <p class="mb-1 text-muted">{{ Str::limit($reminder->description, 100) }}</p>
                                    @endif
                                    @if($reminder->related_item_name)
                                        <small class="text-info"><i class="fa fa-link"></i> {{ $reminder->related_item_name }}</small>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="{{ $reminder->isOverdue() ? 'text-danger font-weight-bold' : ($reminder->remind_at->isToday() ? 'text-warning' : 'text-muted') }}">
                                        @if($reminder->isOverdue())
                                            <i class="fa fa-exclamation-circle"></i>
                                        @endif
                                        {{ $reminder->remind_at->format('d/m/Y H:i') }}
                                        <br><small>{{ $reminder->remind_at->diffForHumans() }}</small>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2">
                                @if(!$reminder->is_completed && !$reminder->is_dismissed)
                                    <form action="{{ route('admin.crm.reminders.complete', $reminder) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check"></i> Completar</button>
                                    </form>
                                    <form action="{{ route('admin.crm.reminders.snooze', $reminder) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="minutes" value="30">
                                        <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-clock-o"></i> +30 min</button>
                                    </form>
                                    <form action="{{ route('admin.crm.reminders.dismiss', $reminder) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary"><i class="fa fa-times"></i> Descartar</button>
                                    </form>
                                @else
                                    @if($reminder->is_completed)
                                        <span class="badge badge-success"><i class="fa fa-check"></i> Completado {{ $reminder->completed_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                    @if($reminder->is_dismissed)
                                        <span class="badge badge-secondary"><i class="fa fa-times"></i> Descartado</span>
                                    @endif
                                @endif
                                <a href="{{ route('admin.crm.reminders.edit', $reminder) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fa fa-bell-slash fa-4x text-muted mb-3"></i>
                        <h5>No hay recordatorios</h5>
                        <p class="text-muted">Crea un recordatorio para no olvidar tareas importantes</p>
                        <a href="{{ route('admin.crm.reminders.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Nuevo Recordatorio
                        </a>
                    </div>
                @endforelse

                {{ $reminders->links() }}
            </div>
        </div>
    </div>
@endsection
