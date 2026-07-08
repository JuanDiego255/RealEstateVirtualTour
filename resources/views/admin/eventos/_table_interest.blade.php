<thead>
    <tr>
        <th>Cliente</th>
        <th>Contacto</th>
        <th>Interés</th>
        <th>Origen</th>
        <th>Agente</th>
        <th>Fecha</th>
        <th class="text-right">Acciones</th>
    </tr>
</thead>
<tbody>
@forelse($records as $lead)
    @php
        $priorityClass = ['hot' => 'urgent', 'high' => 'high', 'medium' => 'medium', 'low' => 'low'][$lead->interest_level] ?? 'medium';
        $waPhone = preg_replace('/\D/', '', (string) $lead->phone);
        $isInCrm = isset($inCrm[$lead->id]);
    @endphp
    <tr data-id="{{ $lead->id }}">
        <td>
            <strong>{{ $lead->name }}</strong>
            @if($lead->property)
                <div style="font-size:12px;color:#888;">{{ trim(($lead->property->brand ?? '') . ' ' . ($lead->property->model ?? '')) ?: $lead->property->name }}</div>
            @endif
        </td>
        <td>
            <div>{{ $lead->phone }}</div>
            @if($lead->email)<div style="font-size:12px;color:#888;">{{ $lead->email }}</div>@endif
        </td>
        <td><span class="crm-badge {{ $priorityClass }}">{{ ucfirst($lead->interest_level ?? 'medium') }}</span></td>
        <td><span class="crm-badge new">{{ ucfirst($lead->source ?? 'kiosk') }}</span></td>
        <td>{{ $lead->capturedBy->name ?? '—' }}</td>
        <td style="font-size:12px;color:#666;">{{ $lead->created_at ? $lead->created_at->format('d/m/Y H:i') : '—' }}</td>
        <td class="text-right" style="white-space:nowrap;">
            @if($waPhone)
                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="action-btn success" title="WhatsApp"><i class="fa fa-whatsapp"></i></a>
            @endif
            <span class="js-crm-cell">
                @if($isInCrm)
                    <span class="crm-badge won"><i class="fa fa-check"></i> En CRM</span>
                @else
                    <button class="action-btn gold js-add-crm" data-id="{{ $lead->id }}"
                            data-url="{{ route('event-leads.add-to-crm', $lead->id) }}">
                        <i class="fa fa-user-plus"></i> Agregar al CRM
                    </button>
                @endif
            </span>
        </td>
    </tr>
@empty
    <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">No hay registros de "Me interesa".</td></tr>
@endforelse
</tbody>
