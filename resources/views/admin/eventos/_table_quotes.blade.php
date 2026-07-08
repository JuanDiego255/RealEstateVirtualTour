<thead>
    <tr>
        <th>Cliente</th>
        <th>Contacto</th>
        <th>Vehículo</th>
        <th>Cuota</th>
        <th>Agente</th>
        <th>Fecha</th>
        <th class="text-right">Acciones</th>
    </tr>
</thead>
<tbody>
@forelse($records as $quote)
    @php
        $waPhone = preg_replace('/\D/', '', (string) $quote->customer_phone);
        $symbol  = ($quote->currency ?? 'CRC') === 'USD' ? '$' : '₡';
        $isInCrm = $quote->customer_phone && isset($inCrm[$quote->customer_phone]);
    @endphp
    <tr data-id="{{ $quote->id }}">
        <td><strong>{{ $quote->customer_name ?? 'Sin nombre' }}</strong></td>
        <td>
            <div>{{ $quote->customer_phone }}</div>
            @if($quote->customer_email)<div style="font-size:12px;color:#888;">{{ $quote->customer_email }}</div>@endif
        </td>
        <td>
            @if($quote->property)
                {{ trim(($quote->property->brand ?? '') . ' ' . ($quote->property->model ?? '')) ?: $quote->property->name }}
            @else
                <span style="color:#999;">—</span>
            @endif
        </td>
        <td>
            <strong>{{ $symbol }}{{ number_format($quote->monthly_payment, 0, ',', '.') }}</strong>
            <div style="font-size:12px;color:#888;">{{ $quote->term_months }} meses</div>
        </td>
        <td>{{ $quote->capturedBy->name ?? '—' }}</td>
        <td style="font-size:12px;color:#666;">{{ $quote->created_at ? $quote->created_at->format('d/m/Y H:i') : '—' }}</td>
        <td class="text-right" style="white-space:nowrap;">
            @if($waPhone)
                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="action-btn success" title="WhatsApp"><i class="fa fa-whatsapp"></i></a>
            @endif
            <a href="{{ route('kiosk.quote.pdf', $quote->id) }}" target="_blank" class="action-btn secondary" title="PDF"><i class="fa fa-file-pdf-o"></i></a>
            <span class="js-crm-cell">
                @if($isInCrm)
                    <span class="crm-badge won"><i class="fa fa-check"></i> En CRM</span>
                @else
                    <button class="action-btn gold js-add-crm" data-id="{{ $quote->id }}"
                            data-url="{{ route('quotes.add-to-crm', $quote->id) }}">
                        <i class="fa fa-user-plus"></i> Crear Lead
                    </button>
                @endif
            </span>
        </td>
    </tr>
@empty
    <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">No hay cotizaciones.</td></tr>
@endforelse
</tbody>
