{{-- Sistema de diseño compartido del CRM. Incluir con @include('admin.crm._ui') --}}
<style>
    .crm-page { padding: 20px; }

    /* Encabezado de página */
    .crm-page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
    .crm-page-header h2 { font-size:20px; font-weight:800; color:#1a1a2e; margin:0; display:flex; align-items:center; gap:8px; }
    .crm-page-header h2 i { color:#c2ac1f; }
    .crm-page-header .sub { font-size:13px; color:#888; margin:4px 0 0; }

    /* Stat cards */
    .crm-stat-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px; }
    .crm-stat-card { background:#fff; border-radius:14px; padding:20px 22px; box-shadow:0 2px 12px rgba(0,0,0,.07); display:flex; align-items:center; gap:16px; border-left:4px solid transparent; }
    .crm-stat-card.bd-blue{border-color:#3b82f6} .crm-stat-card.bd-red{border-color:#ef4444}
    .crm-stat-card.bd-amber{border-color:#f59e0b} .crm-stat-card.bd-violet{border-color:#8b5cf6}
    .crm-stat-card.bd-green{border-color:#22c55e} .crm-stat-card.bd-slate{border-color:#64748b}
    .crm-stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .crm-stat-number { font-size:28px; font-weight:800; color:#1a1a2e; line-height:1; }
    .crm-stat-label { font-size:12px; color:#888; margin-top:2px; }

    /* Iconos de color */
    .ic-blue{background:#dbeafe;color:#1d4ed8} .ic-red{background:#fee2e2;color:#dc2626}
    .ic-amber{background:#fef3c7;color:#b45309} .ic-violet{background:#ede9fe;color:#7c3aed}
    .ic-green{background:#d1fae5;color:#065f46} .ic-slate{background:#f1f5f9;color:#475569}

    /* Section cards */
    .crm-section { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); margin-bottom:22px; overflow:hidden; }
    .crm-section-header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #f0f0f0; gap:12px; flex-wrap:wrap; }
    .crm-section-header h5 { font-size:14px; font-weight:700; color:#1a1a2e; margin:0; display:flex; align-items:center; gap:8px; }
    .crm-section-header h5 i { color:#c2ac1f; }
    .crm-section-header .hint { font-size:12px; color:#94a3b8; font-weight:400; }
    .crm-section-body { padding:0; }
    .crm-section-pad { padding:20px; }

    /* Filas */
    .crm-row-item { display:flex; align-items:center; gap:12px; padding:12px 20px; border-bottom:1px solid #f5f5f5; text-decoration:none; color:inherit; transition:background .12s; }
    .crm-row-item:last-child { border-bottom:none; }
    .crm-row-item:hover { background:#fafafa; text-decoration:none; color:inherit; }
    .crm-row-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
    .crm-row-main { flex:1; min-width:0; }
    .crm-row-title { font-weight:600; font-size:13px; color:#1a1a2e; }
    .crm-row-sub { font-size:11px; color:#888; margin-top:2px; }
    .crm-row-right { text-align:right; flex-shrink:0; display:flex; align-items:center; gap:8px; }

    /* Tabla */
    .crm-table-wrap { overflow-x:auto; }
    .crm-table { width:100%; border-collapse:collapse; }
    .crm-table th { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#94a3b8; font-weight:700; text-align:left; padding:12px 20px; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
    .crm-table td { padding:12px 20px; border-bottom:1px solid #f5f5f5; font-size:13px; color:#1a1a2e; vertical-align:middle; }
    .crm-table tr:last-child td { border-bottom:none; }
    .crm-table tbody tr:hover td { background:#fafafa; }
    .crm-table .num { text-align:right; }
    .crm-table .muted { color:#94a3b8; font-size:12px; }

    /* Badges */
    .crm-badge { display:inline-block; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; }
    .crm-badge.blue{background:#dbeafe;color:#1d4ed8} .crm-badge.green{background:#d1fae5;color:#065f46}
    .crm-badge.red{background:#fee2e2;color:#991b1b} .crm-badge.amber{background:#fef3c7;color:#92400e}
    .crm-badge.violet{background:#ede9fe;color:#7c3aed} .crm-badge.slate{background:#f1f5f9;color:#475569}

    /* Botones */
    .action-btn { padding:6px 12px; border:none; border-radius:8px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; gap:5px; transition:all .15s; text-decoration:none; font-weight:500; }
    .action-btn.primary { background:#1a1a2e; color:#fff; } .action-btn.primary:hover { background:#2d2d4e; color:#fff; }
    .action-btn.success { background:#d1fae5; color:#065f46; } .action-btn.success:hover { background:#a7f3d0; color:#065f46; }
    .action-btn.secondary { background:#f1f5f9; color:#475569; } .action-btn.secondary:hover { background:#e2e8f0; color:#1e293b; }
    .action-btn.danger { background:#fee2e2; color:#991b1b; } .action-btn.danger:hover { background:#fecaca; color:#991b1b; }
    .action-btn.warning { background:#fef3c7; color:#92400e; } .action-btn.warning:hover { background:#fde68a; color:#92400e; }
    .action-btn.xs { padding:4px 9px; font-size:11px; }

    /* Formularios */
    .crm-form-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; }
    .crm-form-group { margin-bottom:16px; }
    .crm-label { font-size:12px; font-weight:600; color:#475569; margin-bottom:5px; display:block; }
    .crm-input, .crm-select, .crm-textarea { width:100%; border:1px solid #e2e8f0; border-radius:10px; padding:9px 12px; font-size:13px; color:#1a1a2e; background:#fff; }
    .crm-input:focus, .crm-select:focus, .crm-textarea:focus { outline:none; border-color:#c2ac1f; box-shadow:0 0 0 3px rgba(194,172,31,.12); }
    .crm-help { font-size:11px; color:#94a3b8; margin-top:4px; }

    /* Toggle */
    .crm-toggle { display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#475569; }
    .crm-toggle input { position:absolute; opacity:0; }
    .crm-toggle .track { width:38px; height:22px; border-radius:22px; background:#cbd5e1; position:relative; transition:.15s; flex-shrink:0; }
    .crm-toggle .track::after { content:''; position:absolute; top:2px; left:2px; width:18px; height:18px; border-radius:50%; background:#fff; transition:.15s; }
    .crm-toggle input:checked + .track { background:#22c55e; }
    .crm-toggle input:checked + .track::after { transform:translateX(16px); }

    /* Progreso */
    .crm-progress { height:8px; border-radius:6px; background:#f1f5f9; overflow:hidden; }
    .crm-progress > span { display:block; height:100%; border-radius:6px; }
    .pg-green{background:#22c55e} .pg-amber{background:#f59e0b} .pg-red{background:#ef4444}

    /* Alertas */
    .crm-alert { border-radius:12px; padding:12px 16px; font-size:13px; margin-bottom:18px; }
    .crm-alert.success { background:#d1fae5; color:#065f46; }
    .crm-alert.danger { background:#fee2e2; color:#991b1b; }
    .crm-alert.info { background:#dbeafe; color:#1e40af; }
    .crm-alert.warning { background:#fef3c7; color:#92400e; }

    /* Estado vacío */
    .empty-state { padding:32px; text-align:center; color:#ccc; font-size:13px; }
    .empty-state i { font-size:28px; display:block; margin-bottom:8px; }

    .crm-two-col { display:grid; grid-template-columns:1fr 1fr; gap:22px; }
    @media(max-width:900px){ .crm-two-col { grid-template-columns:1fr; } }
</style>
