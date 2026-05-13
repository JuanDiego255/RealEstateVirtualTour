{{--
  PDF Report Header Partial
  Variables requeridas: $company, $logoPath, $reportTitle, $filters, $printDate
--}}
<table width="100%" style="border-bottom:2px solid #2c3e50; margin-bottom:16px; padding-bottom:10px;">
    <tr>
        <td width="70%" style="vertical-align:middle;">
            @if(!empty($logoPath))
                <img src="{{ $logoPath }}" style="max-height:48px; max-width:160px; margin-bottom:4px;"><br>
            @endif
            <span style="font-size:18px; font-weight:bold; color:#2c3e50;">
                {{ $company->name ?? 'Empresa' }}
            </span>
            @if(!empty($company->legal_name) && $company->legal_name !== $company->name)
                <br><span style="font-size:10px; color:#666;">{{ $company->legal_name }}</span>
            @endif
            @if(!empty($company->address))
                <br><span style="font-size:9px; color:#888;">{{ $company->address }}</span>
            @endif
            @if(!empty($company->phone))
                <span style="font-size:9px; color:#888;">  ·  Tel: {{ $company->phone }}</span>
            @endif
        </td>
        <td width="30%" style="vertical-align:middle; text-align:right;">
            <span style="font-size:13px; font-weight:bold; color:#2c3e50;">{{ $reportTitle }}</span>
            <br><span style="font-size:9px; color:#888;">Generado: {{ $printDate }}</span>
        </td>
    </tr>
</table>

{{-- Filtros aplicados --}}
@if(!empty($filters))
<table width="100%" style="margin-bottom:14px; background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px;">
    <tr>
        <td style="padding:6px 10px;">
            <span style="font-size:9px; font-weight:bold; color:#6c757d; text-transform:uppercase;">Filtros aplicados: </span>
            @foreach($filters as $label => $value)
                <span style="font-size:9px; background:#dee2e6; padding:2px 6px; border-radius:3px; margin-right:4px;">
                    <strong>{{ $label }}:</strong> {{ $value }}
                </span>
            @endforeach
        </td>
    </tr>
</table>
@else
<div style="margin-bottom:14px;"></div>
@endif
