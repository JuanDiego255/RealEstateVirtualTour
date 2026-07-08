@extends('admin.main')
@section('title', 'Eventos - Registros')

@include('admin.crm.layouts._crm-styles')

@section('content')
<div class="crm-page">

    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-calendar-check-o"></i> Registros de Eventos</h2>
            <div class="sub">Todos los clientes interesados y cotizaciones capturados en el kiosko.</div>
        </div>
        <div class="actions">
            <div class="rt-indicator"><span class="rt-dot"></span> Tiempo real</div>
            <button class="action-btn gold" id="btn-bulk-crm">
                <i class="fa fa-users"></i> Integrar todos al CRM
            </button>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="stats-grid" id="eventos-stats">
        @include('admin.eventos._stats', ['type' => $type, 'stats' => $stats])
    </div>

    {{-- Tabla --}}
    <div class="dashboard-card">
        <div class="dc-header" style="gap:12px;flex-wrap:wrap;">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                {{-- Dropdown de tipo --}}
                <select id="eventos-type" class="form-control" style="width:auto;min-width:170px;border-radius:8px;height:38px;">
                    <option value="interest" {{ $type === 'interest' ? 'selected' : '' }}>Me interesa</option>
                    <option value="quotes"   {{ $type === 'quotes' ? 'selected' : '' }}>Cotizaciones</option>
                </select>
                {{-- Filtro en tiempo real por nombre --}}
                <input type="text" id="eventos-search" class="form-control"
                       placeholder="Buscar por nombre, teléfono o email..."
                       value="{{ $search }}" style="width:280px;max-width:100%;border-radius:8px;height:38px;">
            </div>
            <span id="eventos-loading" class="d-none" style="font-size:12px;color:#888;">
                <i class="fa fa-spinner fa-spin"></i> Cargando...
            </span>
        </div>
        <div class="dc-body" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="crm-table" id="eventos-table">
                    @include($partial, ['records' => $records, 'inCrm' => $inCrm])
                </table>
            </div>
            <div id="eventos-pagination" style="padding:14px 18px;">
                {!! $records->links() !!}
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="crm-toast" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;
     background:#1a1a2e;color:#fff;padding:13px 20px;border-radius:10px;
     box-shadow:0 8px 24px rgba(0,0,0,.25);font-size:14px;max-width:340px;"></div>
@endsection

@push('script')
<script>
(function () {
    'use strict';

    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var INDEX_URL = '{{ route('admin.eventos.index') }}';
    var BULK_URL  = '{{ route('admin.eventos.bulk-to-crm') }}';
    var debounceTimer = null;

    function showToast(msg, type) {
        var t = document.getElementById('crm-toast');
        t.textContent = msg;
        t.style.background = type === 'danger' ? '#991b1b' : (type === 'warning' ? '#92400e' : '#1a1a2e');
        t.style.display = 'block';
        setTimeout(function () { t.style.display = 'none'; }, 3800);
    }

    function currentType()  { return document.getElementById('eventos-type').value; }
    function currentSearch(){ return document.getElementById('eventos-search').value; }

    function fetchRecords(page) {
        var params = new URLSearchParams({
            type: currentType(),
            search: currentSearch(),
            page: page || 1,
            ajax: '1'
        });
        document.getElementById('eventos-loading').classList.remove('d-none');
        fetch(INDEX_URL + '?' + params, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.getElementById('eventos-table').innerHTML = data.table_html;
                document.getElementById('eventos-pagination').innerHTML = data.pagination;
                document.getElementById('eventos-stats').innerHTML = data.stats_html;
                bindRows();
                bindPagination();
                history.replaceState(null, '', '?type=' + currentType() + '&search=' + encodeURIComponent(currentSearch()));
            })
            .catch(function () { showToast('Error al cargar registros', 'danger'); })
            .finally(function () { document.getElementById('eventos-loading').classList.add('d-none'); });
    }

    function bindRows() {
        document.querySelectorAll('.js-add-crm').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url  = btn.getAttribute('data-url');
                var cell = btn.closest('.js-crm-cell');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            cell.innerHTML = '<span class="crm-badge won"><i class="fa fa-check"></i> En CRM</span>';
                            showToast(data.message || 'Agregado al CRM');
                        } else {
                            // Ya existía: igualmente reflejar que está en el CRM
                            cell.innerHTML = '<span class="crm-badge won"><i class="fa fa-check"></i> En CRM</span>';
                            showToast(data.message || 'Ya existe en el CRM', 'warning');
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-user-plus"></i> Agregar al CRM';
                        showToast('Error al agregar al CRM', 'danger');
                    });
            });
        });
    }

    function bindPagination() {
        document.querySelectorAll('#eventos-pagination a').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var url = new URL(a.href);
                fetchRecords(url.searchParams.get('page') || 1);
            });
        });
    }

    // Filtros
    document.getElementById('eventos-search').addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () { fetchRecords(1); }, 350);
    });
    document.getElementById('eventos-type').addEventListener('change', function () {
        document.getElementById('eventos-search').value = '';
        fetchRecords(1);
    });

    // Integración masiva
    document.getElementById('btn-bulk-crm').addEventListener('click', function () {
        var typeLabel = currentType() === 'quotes' ? 'las cotizaciones' : 'los registros "Me interesa"';
        if (!confirm('¿Integrar TODOS ' + typeLabel + ' al CRM? Los que ya existen se omitirán.')) return;
        var btn = this;
        btn.disabled = true;
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Integrando...';
        fetch(BULK_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: currentType() })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showToast(data.message || 'Integración completada');
                fetchRecords(1);
            })
            .catch(function () { showToast('Error en la integración masiva', 'danger'); })
            .finally(function () { btn.disabled = false; btn.innerHTML = original; });
    });

    // Inicial
    bindRows();
    bindPagination();
})();
</script>
@endpush
