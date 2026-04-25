import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/event_lead_model.dart';
import 'package:space360_flutter/src/pages/agent/dashboard/lead_detail_sheet.dart';
import 'package:space360_flutter/src/services/agent_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class EventDashboardPage extends StatefulWidget {
  final AuthUser user;
  const EventDashboardPage({super.key, required this.user});

  @override
  State<EventDashboardPage> createState() => _EventDashboardPageState();
}

class _EventDashboardPageState extends State<EventDashboardPage> {
  final _service = AgentService();
  Resource<DashboardStats>? _stats;
  Resource<List<EventLeadModel>>? _leads;

  // filter state
  String _filterInterest = 'all';  // all | low | medium | high | hot
  String _filterContact  = 'all';  // all | pending | contacted

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _stats = null; _leads = null; });
    final results = await Future.wait([
      _service.getDashboardStats(),
      _service.getDashboardLeads(),
    ]);
    if (!mounted) return;
    setState(() {
      _stats = results[0] as Resource<DashboardStats>;
      _leads = results[1] as Resource<List<EventLeadModel>>;
    });
  }

  List<EventLeadModel> _applyFilters(List<EventLeadModel> all) {
    return all.where((l) {
      if (_filterInterest != 'all' && l.interestLevel != _filterInterest) return false;
      if (_filterContact == 'pending'   && l.contacted)  return false;
      if (_filterContact == 'contacted' && !l.contacted) return false;
      return true;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text('Dashboard Evento',
            style: TextStyle(color: _kText, fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _kSubtext),
            onPressed: _load,
          ),
        ],
      ),
      body: _stats == null
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)))
          : RefreshIndicator(
              color: _kGold,
              backgroundColor: _kSurface,
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // ── Stats ──────────────────────────────────────────
                  if (_stats is AppError<DashboardStats>)
                    ErrorState(message: (_stats as AppError<DashboardStats>).message, onRetry: _load)
                  else if (_stats is Success<DashboardStats>)
                    _StatsGrid(stats: (_stats as Success<DashboardStats>).data),
                  const SizedBox(height: 24),
                  // ── Leads header + filters ─────────────────────────
                  Row(
                    children: [
                      const Text('Leads', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
                      if (_leads is Success<List<EventLeadModel>>) ...[
                        const SizedBox(width: 6),
                        Text(
                          '(${_applyFilters((_leads as Success<List<EventLeadModel>>).data).length})',
                          style: const TextStyle(color: _kSubtext, fontSize: 14),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 10),
                  _FilterRow(
                    filterInterest: _filterInterest,
                    filterContact:  _filterContact,
                    onInterestChanged: (v) => setState(() => _filterInterest = v),
                    onContactChanged:  (v) => setState(() => _filterContact  = v),
                  ),
                  const SizedBox(height: 12),
                  _buildLeads(),
                ],
              ),
            ),
    );
  }

  Widget _buildLeads() {
    if (_leads == null) {
      return const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)));
    }
    return switch (_leads!) {
      AppError(:final message) => ErrorState(message: message, onRetry: _load),
      Success(:final data) when data.isEmpty =>
        const EmptyState(message: 'No hay leads registrados', icon: Icons.people_outline),
      Success(:final data) => _buildLeadList(_applyFilters(data)),
      _ => const SizedBox(),
    };
  }

  Widget _buildLeadList(List<EventLeadModel> leads) {
    if (leads.isEmpty) {
      return const EmptyState(
        message: 'Ningún lead coincide con los filtros',
        icon: Icons.filter_list_off_rounded,
      );
    }
    return Column(
      children: leads
          .map((l) => _LeadCard(
                lead: l,
                user: widget.user,
                onContacted: _load,
                onTap: () => showLeadDetailSheet(context, l, widget.user),
              ))
          .toList(),
    );
  }
}

// ─── Stats grid ───────────────────────────────────────────────────────────────

class _StatsGrid extends StatelessWidget {
  final DashboardStats stats;
  const _StatsGrid({required this.stats});

  @override
  Widget build(BuildContext context) => GridView.count(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 1.6,
        children: [
          _StatCard(label: 'Total leads',    value: '${stats.totalLeads}',   icon: Icons.people_rounded,              color: _kGold),
          _StatCard(label: 'Hoy',            value: '${stats.leadsToday}',   icon: Icons.today_rounded,               color: const Color(0xFF3498DB)),
          _StatCard(label: '🔥 Hot',         value: '${stats.leadsHot}',     icon: Icons.local_fire_department_rounded, color: const Color(0xFFC62828)),
          _StatCard(label: 'Sin contactar',  value: '${stats.leadsPending}', icon: Icons.mark_email_unread_outlined,  color: const Color(0xFFE67E22)),
        ],
      );
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatCard({required this.label, required this.value, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 6),
            Text(value, style: TextStyle(color: color, fontSize: 22, fontWeight: FontWeight.bold)),
            Text(label, style: const TextStyle(color: _kSubtext, fontSize: 11)),
          ],
        ),
      );
}

// ─── Filter row ───────────────────────────────────────────────────────────────

class _FilterRow extends StatelessWidget {
  final String filterInterest;
  final String filterContact;
  final ValueChanged<String> onInterestChanged;
  final ValueChanged<String> onContactChanged;
  const _FilterRow({
    required this.filterInterest,
    required this.filterContact,
    required this.onInterestChanged,
    required this.onContactChanged,
  });

  static const _interestFilters = [
    ('all', 'Todos'),
    ('hot', '🔥 Hot'),
    ('high', 'Alto'),
    ('medium', 'Medio'),
    ('low', 'Bajo'),
  ];

  static const _contactFilters = [
    ('all', 'Todos'),
    ('pending', 'Sin contactar'),
    ('contacted', 'Contactados'),
  ];

  static const _interestColors = {
    'hot': Color(0xFFC62828), 'high': Color(0xFFE65100),
    'medium': Color(0xFFF57F17), 'low': Color(0xFF2E7D32),
    'all': _kGold,
  };

  @override
  Widget build(BuildContext context) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: _interestFilters.map((f) {
                final sel = filterInterest == f.$1;
                final col = _interestColors[f.$1] ?? _kSubtext;
                return GestureDetector(
                  onTap: () => onInterestChanged(f.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: sel ? col.withOpacity(0.2) : _kSurface,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: sel ? col : Colors.white12),
                    ),
                    child: Text(f.$2,
                        style: TextStyle(
                          color: sel ? col : _kSubtext,
                          fontSize: 12, fontWeight: sel ? FontWeight.bold : FontWeight.normal)),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: _contactFilters.map((f) {
              final sel = filterContact == f.$1;
              return GestureDetector(
                onTap: () => onContactChanged(f.$1),
                child: Container(
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                  decoration: BoxDecoration(
                    color: sel ? _kGold.withOpacity(0.12) : _kSurface,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: sel ? _kGold : Colors.white12),
                  ),
                  child: Text(f.$2,
                      style: TextStyle(
                        color: sel ? _kGold : _kSubtext,
                        fontSize: 11, fontWeight: sel ? FontWeight.bold : FontWeight.normal)),
                ),
              );
            }).toList(),
          ),
        ],
      );
}

// ─── Lead card ────────────────────────────────────────────────────────────────

class _LeadCard extends StatelessWidget {
  final EventLeadModel lead;
  final AuthUser user;
  final VoidCallback onContacted;
  final VoidCallback onTap;

  const _LeadCard({
    required this.lead,
    required this.user,
    required this.onContacted,
    required this.onTap,
  });

  static const _interestColors = {
    'hot': Color(0xFFC62828), 'high': Color(0xFFE65100),
    'medium': Color(0xFFF57F17), 'low': Color(0xFF2E7D32),
  };
  static const _interestLabels = {
    'hot': '🔥 Hot', 'high': 'Alto', 'medium': 'Medio', 'low': 'Bajo',
  };

  Color get _iColor => _interestColors[lead.interestLevel] ?? _kSubtext;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: lead.contacted
                ? Colors.white.withOpacity(0.05)
                : _iColor.withOpacity(0.3),
          ),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
              child: Text(lead.name,
                  style: TextStyle(
                    color: lead.contacted ? _kSubtext : _kText,
                    fontSize: 15, fontWeight: FontWeight.bold,
                  )),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: _iColor.withOpacity(0.15),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                _interestLabels[lead.interestLevel] ?? lead.interestLevel,
                style: TextStyle(color: _iColor, fontSize: 11, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(width: 6),
            const Icon(Icons.chevron_right_rounded, color: _kSubtext, size: 18),
          ]),
          const SizedBox(height: 6),
          Row(children: [
            const Icon(Icons.phone_outlined, size: 13, color: _kSubtext),
            const SizedBox(width: 4),
            Text(lead.phone, style: const TextStyle(color: _kSubtext, fontSize: 13)),
            if (lead.email != null) ...[
              const SizedBox(width: 12),
              const Icon(Icons.email_outlined, size: 13, color: _kSubtext),
              const SizedBox(width: 4),
              Expanded(
                child: Text(lead.email!,
                    style: const TextStyle(color: _kSubtext, fontSize: 12),
                    overflow: TextOverflow.ellipsis),
              ),
            ],
          ]),
          if (lead.vehicle != null) ...[
            const SizedBox(height: 4),
            Row(children: [
              const Icon(Icons.directions_car_outlined, size: 13, color: _kSubtext),
              const SizedBox(width: 4),
              Text(lead.vehicle!.name,
                  style: const TextStyle(color: _kSubtext, fontSize: 12)),
            ]),
          ],
          const SizedBox(height: 8),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text(_formatDate(lead.createdAt),
                style: const TextStyle(color: _kSubtext, fontSize: 11)),
            if (lead.contacted)
              Row(children: const [
                Icon(Icons.check_circle_rounded, color: Color(0xFF2E7D32), size: 14),
                SizedBox(width: 4),
                Text('Contactado', style: TextStyle(color: Color(0xFF2E7D32), fontSize: 11)),
              ])
            else if (user.canAccess('event_dashboard', 'mark_contacted'))
              GestureDetector(
                onTap: () => _markContacted(context),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: _kGold.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: _kGold.withOpacity(0.4)),
                  ),
                  child: const Text('Marcar contactado',
                      style: TextStyle(color: _kGold, fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ),
          ]),
        ]),
      ),
    );
  }

  Future<void> _markContacted(BuildContext context) async {
    final result = await AgentService().markLeadContacted(lead.id);
    if (result is Success<bool>) {
      Fluttertoast.showToast(
          msg: 'Marcado como contactado', backgroundColor: const Color(0xFF2E7D32));
      onContacted();
    } else if (result is AppError) {
      Fluttertoast.showToast(
          msg: (result as AppError<bool>).message, backgroundColor: Colors.red[700]);
    }
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2,'0')}/${dt.month.toString().padLeft(2,'0')} '
          '${dt.hour.toString().padLeft(2,'0')}:${dt.minute.toString().padLeft(2,'0')}';
    } catch (_) {
      return iso;
    }
  }
}
