@extends('admin.main')
@section('title', 'CRM - Pipeline')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-columns"></i> Pipeline de Leads</h4>
            <div>
                <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary"><i class="fa fa-list"></i> Vista Lista</a>
                <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo Lead</a>
            </div>
        </div>

        <div class="pipeline-container" style="overflow-x: auto;">
            <div class="d-flex" style="min-width: max-content;">
                @foreach($statuses as $statusKey => $statusLabel)
                    <div class="pipeline-column mr-3" style="min-width: 280px; max-width: 280px;">
                        <div class="card">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center
                                @switch($statusKey)
                                    @case('new') bg-info text-white @break
                                    @case('contacted') bg-primary text-white @break
                                    @case('qualified') bg-warning @break
                                    @case('proposal') bg-secondary text-white @break
                                    @case('negotiation') bg-dark text-white @break
                                    @case('won') bg-success text-white @break
                                    @case('lost') bg-danger text-white @break
                                @endswitch
                            ">
                                <strong>{{ $statusLabel }}</strong>
                                <span class="badge badge-light">{{ $leadsByStatus[$statusKey]->count() }}</span>
                            </div>
                            <div class="card-body p-2 pipeline-cards" data-status="{{ $statusKey }}" style="min-height: 400px; max-height: 70vh; overflow-y: auto;">
                                @foreach($leadsByStatus[$statusKey] as $lead)
                                    <div class="card mb-2 pipeline-card" data-lead-id="{{ $lead->id }}" style="cursor: pointer;">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong class="text-truncate" style="max-width: 180px;">{{ $lead->name }}</strong>
                                                <span class="badge badge-{{ $lead->priority_color }} badge-sm">{{ $lead->priority_label }}</span>
                                            </div>
                                            @if($lead->phone || $lead->email)
                                                <small class="text-muted d-block text-truncate">
                                                    {{ $lead->phone ?? $lead->email }}
                                                </small>
                                            @endif
                                            @if($lead->property)
                                                <small class="text-info d-block text-truncate">
                                                    <i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 25) }}
                                                </small>
                                            @endif
                                            @if($lead->vehicle)
                                                <small class="text-info d-block text-truncate">
                                                    <i class="fa fa-car"></i> {{ Str::limit($lead->vehicle->name, 25) }}
                                                </small>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i> {{ $lead->user->name ?? '-' }}
                                                </small>
                                                @if($lead->next_follow_up)
                                                    <small class="{{ $lead->isOverdueForFollowUp() ? 'text-danger' : 'text-muted' }}">
                                                        <i class="fa fa-clock-o"></i> {{ $lead->next_follow_up->format('d/m') }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary btn-block">
                                                    <i class="fa fa-eye"></i> Ver
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($leadsByStatus[$statusKey]->count() === 0)
                                    <div class="text-center text-muted py-4">
                                        <i class="fa fa-inbox fa-2x"></i>
                                        <p class="mb-0 mt-2">Sin leads</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('script')
<style>
    .pipeline-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        transition: all 0.2s;
    }
    .pipeline-cards {
        background: #f8f9fa;
        border-radius: 0 0 4px 4px;
    }
</style>
@endpush
