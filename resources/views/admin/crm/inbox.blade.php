@extends('admin.main')
@section('title', 'Bandeja — Sin atender')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-inbox"></i> Bandeja de trabajo @if($isAdmin)<small class="text-muted">(toda la empresa)</small>@endif</h4>
        <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos los leads</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row mb-4">
        <div class="col-md-4"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Sin contactar</div>
            <h3 class="mb-0 {{ $counts['uncontacted'] ? 'text-primary' : 'text-muted' }}">{{ $counts['uncontacted'] }}</h3>
        </div></div></div>
        <div class="col-md-4"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Tareas vencidas</div>
            <h3 class="mb-0 {{ $counts['tasks'] ? 'text-danger' : 'text-muted' }}">{{ $counts['tasks'] }}</h3>
        </div></div></div>
        <div class="col-md-4"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Recordatorios vencidos</div>
            <h3 class="mb-0 {{ $counts['reminders'] ? 'text-warning' : 'text-muted' }}">{{ $counts['reminders'] }}</h3>
        </div></div></div>
    </div>

    {{-- Leads sin contactar --}}
    <div class="card mb-4">
        <div class="card-header"><strong>Leads sin contactar</strong> <span class="text-muted small">— ordenados por score</span></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Lead</th><th>Origen</th><th>Estado</th>@if($isAdmin)<th>Asesor</th>@endif<th class="text-right">Score</th><th>Ingreso</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($uncontacted as $lead)
                        <tr>
                            <td><strong>{{ $lead->name ?: 'Sin nombre' }}</strong>@if($lead->phone)<div class="small text-muted">{{ $lead->phone }}</div>@endif</td>
                            <td><span class="badge badge-light">{{ \App\Lead::getSources()[$lead->source] ?? $lead->source }}</span></td>
                            <td><span class="badge badge-info">{{ \App\Lead::getStatuses()[$lead->status] ?? $lead->status }}</span></td>
                            @if($isAdmin)<td class="small">{{ optional($lead->user)->name ?: '—' }}</td>@endif
                            <td class="text-right"><strong>{{ (int) $lead->score }}</strong></td>
                            <td class="small text-muted">{{ optional($lead->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-right"><a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-xs btn-outline-primary">Atender</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center text-muted py-4">Nada sin contactar. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        {{-- Tareas vencidas --}}
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Tareas vencidas</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <tbody>
                            @forelse($overdueTasks as $task)
                                <tr>
                                    <td>
                                        <div><strong>{{ $task->title }}</strong></div>
                                        <div class="small text-muted">
                                            {{ optional($task->lead)->name }} ·
                                            venció {{ optional($task->due_at)->format('d/m/Y H:i') }}
                                            @if($isAdmin && $task->assignee) · {{ $task->assignee->name }}@endif
                                        </div>
                                    </td>
                                    <td class="text-right align-middle">
                                        @if($task->lead)<a href="{{ route('admin.crm.leads.show', $task->lead) }}" class="btn btn-xs btn-outline-danger">Ver</a>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted py-4">Sin tareas vencidas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recordatorios vencidos --}}
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Recordatorios vencidos</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <tbody>
                            @forelse($dueReminders as $rem)
                                <tr>
                                    <td>
                                        <div><strong>{{ $rem->title }}</strong></div>
                                        <div class="small text-muted">
                                            {{ $rem->related_item_name }} ·
                                            {{ optional($rem->remind_at)->format('d/m/Y H:i') }}
                                            @if($isAdmin && $rem->user) · {{ $rem->user->name }}@endif
                                        </div>
                                    </td>
                                    <td class="text-right align-middle">
                                        @if($rem->remindable instanceof \App\Lead)
                                            <a href="{{ route('admin.crm.leads.show', $rem->remindable) }}" class="btn btn-xs btn-outline-warning">Ver</a>
                                        @else
                                            <a href="{{ route('admin.crm.reminders.index') }}" class="btn btn-xs btn-outline-warning">Ver</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted py-4">Sin recordatorios vencidos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
