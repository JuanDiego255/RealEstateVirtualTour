<style>
/* ══════════════════════════════════════════════
   CRM Shared Styles — basado en event-dashboard
   ══════════════════════════════════════════════ */

/* Wrapper */
.crm-page { padding: 20px; }

/* Header */
.crm-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 26px;
    flex-wrap: wrap;
    gap: 14px;
}
.crm-page-header h2 {
    font-size: 22px; font-weight: 700;
    color: #1a1a2e; margin: 0 0 4px;
    display: flex; align-items: center; gap: 10px;
}
.crm-page-header h2 i { color: #c2ac1f; }
.crm-page-header .sub { font-size: 13px; color: #888; }
.crm-page-header .actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

/* Cards */
.dashboard-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    overflow: hidden;
    margin-bottom: 18px;
}
.dashboard-card .dc-header {
    padding: 14px 18px;
    border-bottom: 1px solid #f0f0f0;
    display: flex; justify-content: space-between; align-items: center;
}
.dashboard-card .dc-header h5 {
    font-size: 14px; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
    margin: 0; color: #1a1a2e;
}
.dashboard-card .dc-header h5 i { color: #c2ac1f; }
.dashboard-card .dc-body { padding: 16px 18px; }

/* Stat cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    position: relative; overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
.stat-card::before {
    content: '';
    position: absolute; top: 0; right: 0;
    width: 70px; height: 70px;
    background: linear-gradient(135deg, rgba(194,172,31,.1), transparent);
    border-radius: 0 0 0 100%;
}
.stat-card .sc-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 12px; font-size: 20px;
}
.sc-icon.blue   { background: rgba(59,130,246,.13);  color: #3b82f6; }
.sc-icon.green  { background: rgba(34,197,94,.13);   color: #22c55e; }
.sc-icon.yellow { background: rgba(234,179,8,.13);   color: #eab308; }
.sc-icon.red    { background: rgba(239,68,68,.13);   color: #ef4444; }
.sc-icon.purple { background: rgba(168,85,247,.13);  color: #a855f7; }
.sc-icon.orange { background: rgba(249,115,22,.13);  color: #f97316; }
.sc-icon.gold   { background: rgba(194,172,31,.15);  color: #c2ac1f; }
.sc-icon.indigo { background: rgba(99,102,241,.13);  color: #6366f1; }
.stat-card .sc-value { font-size: 30px; font-weight: 700; color: #1a1a2e; line-height: 1; }
.stat-card .sc-label { font-size: 13px; color: #666; margin-top: 4px; }
.stat-card .sc-sub   { font-size: 11px; color: #999; margin-top: 8px; }

/* Status / priority pills */
.crm-badge {
    display: inline-block;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
}
.crm-badge.new         { background: #dbeafe; color: #1d4ed8; }
.crm-badge.contacted   { background: #e0e7ff; color: #4338ca; }
.crm-badge.qualified   { background: #ede9fe; color: #7c3aed; }
.crm-badge.proposal    { background: #fff3cd; color: #b45309; }
.crm-badge.negotiation { background: #fef3c7; color: #92400e; }
.crm-badge.won         { background: #d1fae5; color: #065f46; }
.crm-badge.lost        { background: #fee2e2; color: #991b1b; }
.crm-badge.low         { background: #f1f5f9; color: #64748b; }
.crm-badge.medium      { background: #dbeafe; color: #1e40af; }
.crm-badge.high        { background: #fef3c7; color: #92400e; }
.crm-badge.urgent      { background: #fee2e2; color: #991b1b; }
/* Appointment statuses */
.crm-badge.scheduled   { background: #dbeafe; color: #1d4ed8; }
.crm-badge.confirmed   { background: #d1fae5; color: #065f46; }
.crm-badge.completed   { background: #e0e7ff; color: #4338ca; }
.crm-badge.cancelled   { background: #fee2e2; color: #991b1b; }
.crm-badge.no_show     { background: #fef3c7; color: #92400e; }
/* Reminder priorities */
.crm-badge.normal      { background: #dbeafe; color: #1d4ed8; }
.crm-badge.important   { background: #fef3c7; color: #92400e; }
.crm-badge.critical    { background: #fee2e2; color: #991b1b; }

/* Action buttons */
.action-btn {
    padding: 7px 14px; border: none; border-radius: 9px;
    cursor: pointer; font-size: 13px;
    display: inline-flex; align-items: center; gap: 6px;
    transition: all .15s; text-decoration: none;
    font-weight: 500; line-height: 1;
}
.action-btn.primary   { background: #1a1a2e; color: #fff; }
.action-btn.primary:hover { background: #2d2d4e; color: #fff; text-decoration: none; }
.action-btn.success   { background: #d1fae5; color: #065f46; }
.action-btn.success:hover { background: #a7f3d0; color: #065f46; text-decoration: none; }
.action-btn.danger    { background: #fee2e2; color: #991b1b; }
.action-btn.danger:hover  { background: #fecaca; color: #991b1b; text-decoration: none; }
.action-btn.warning   { background: #fef3c7; color: #92400e; }
.action-btn.warning:hover { background: #fde68a; color: #92400e; text-decoration: none; }
.action-btn.secondary { background: #f1f5f9; color: #475569; }
.action-btn.secondary:hover { background: #e2e8f0; color: #1e293b; text-decoration: none; }
.action-btn.gold      { background: #fef9e7; color: #92400e; border: 1px solid #c2ac1f; }
.action-btn.gold:hover { background: #c2ac1f; color: #fff; text-decoration: none; }
.action-btn.view      { background: #f1f5f9; color: #475569; padding: 5px 9px; font-size: 12px; }
.action-btn.view:hover { background: #e2e8f0; text-decoration: none; }
.action-btn.more      { background: #f5f3ff; color: #6d28d9; padding: 5px 9px; font-size: 12px; }
.action-btn.more:hover { background: #ede9fe; text-decoration: none; }

/* Tables */
.crm-table { width: 100%; border-collapse: collapse; }
.crm-table th {
    text-align: left; padding: 11px 14px;
    font-size: 11px; text-transform: uppercase;
    letter-spacing: .4px; color: #888;
    border-bottom: 2px solid #f0f0f0;
    background: #fafafa; font-weight: 600;
}
.crm-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #f5f5f5;
    font-size: 13.5px; vertical-align: middle;
}
.crm-table tr:hover td { background: #fffdf0; }
.crm-table tr.row-danger td { background: #fff5f5; }
.crm-table tr.row-warning td { background: #fffbeb; }

/* Pagination fix */
#pagination-wrap svg { width: 10px !important; height: 10px !important; vertical-align: middle; }
#pagination-wrap .pagination { margin: 0; flex-wrap: wrap; }
#pagination-wrap .page-item .page-link { font-size: 13px; padding: 5px 11px; border-radius: 7px; margin: 0 2px; }

/* Followup date helpers */
.fu-overdue { color: #dc2626; font-weight: 600; font-size: 12px; }
.fu-today   { color: #d97706; font-weight: 600; font-size: 12px; }
.fu-ok      { color: #6b7280; font-size: 12px; }

/* Real-time indicator */
.rt-indicator { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #22c55e; }
.rt-dot { width: 7px; height: 7px; background: #22c55e; border-radius: 50%; animation: rtpulse 1.5s infinite; }
@keyframes rtpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
</style>
