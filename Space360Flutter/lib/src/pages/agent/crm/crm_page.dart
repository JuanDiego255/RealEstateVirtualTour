import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:space360_flutter/src/models/appointment_model.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/models/reminder_model.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_agenda_page.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_analytics_page.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_lead_detail_page.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_pipeline_page.dart';
import 'package:space360_flutter/src/pages/agent/crm/create_lead_sheet.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class CrmPage extends StatefulWidget {
  final AuthUser user;
  const CrmPage({super.key, required this.user});

  @override
  State<CrmPage> createState() => _CrmPageState();
}

class _CrmPageState extends State<CrmPage> with SingleTickerProviderStateMixin {
  late final TabController _tab;
  final _service = CrmService();
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 4, vsync: this);
    _load(reset: true);
    if (widget.user.isAdmin) _loadAgents();
  }

  Future<void> _loadAgents() async {
    final r = await _service.getAgents();
    if (!mounted) return;
    if (r is Success<List<CrmAgentModel>>) setState(() => _agents = r.data);
  }

  @override
  void dispose() {
    _tab.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Resource<CrmLeadPage>? _result;
  String _filterStatus = 'all';
  String _filterOrigin = 'all'; // all | event | agency
  int? _filterAgentId;
  int _page = 1;
  bool _loadingMore = false;
  final List<CrmLead> _leads = [];
  CrmStats? _stats;
  int _lastPage = 1;
  List<CrmAgentModel> _agents = [];

  // Today summary
  List<AppointmentModel> _todayAppts = [];
  List<ReminderModel> _overdueReminders = [];

  static const _statusOptions = [
    ('all',         'Todos'),
    ('new',         'Nuevos'),
    ('contacted',   'Contactado'),
    ('qualified',   'Calificado'),
    ('proposal',    'Propuesta'),
    ('negotiation', 'Negociación'),
    ('won',         'Ganado'),
    ('lost',        'Perdido'),
  ];

  static const _originOptions = [
    ('all',    'Todos'),
    ('event',  'Eventos'),
    ('agency', 'Agencia'),
  ];

  Future<void> _load({bool reset = false}) async {
    if (reset) {
      _page = 1;
      _leads.clear();
    }
    setState(() => _result = null);
    final results = await Future.wait([
      _service.getLeads(
        status:  _filterStatus == 'all' ? null : _filterStatus,
        origin:  _filterOrigin == 'all' ? null : _filterOrigin,
        search:  _searchCtrl.text.trim().isEmpty ? null : _searchCtrl.text.trim(),
        agentId: _filterAgentId,
        page:    _page,
      ),
      if (reset) ...[
        _service.getAgenda(days: 1),
        _service.getReminders(),
      ],
    ]);
    if (!mounted) return;
    setState(() {
      final r = results[0] as Resource<CrmLeadPage>;
      _result = r;
      if (r is Success<CrmLeadPage>) {
        if (reset) _leads.clear();
        _leads.addAll(r.data.leads);
        _stats    = r.data.stats;
        _lastPage = r.data.lastPage;
      }
      if (reset && results.length > 1) {
        final agendaR = results[1] as Resource<List<AppointmentModel>>;
        if (agendaR is Success<List<AppointmentModel>>) _todayAppts = agendaR.data;
        final remR = results[2] as Resource<List<ReminderModel>>;
        if (remR is Success<List<ReminderModel>>) {
          _overdueReminders = remR.data.where((r) => r.isOverdue).toList();
        }
      }
    });
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    _page++;
    final r = await _service.getLeads(
      status:  _filterStatus == 'all' ? null : _filterStatus,
      origin:  _filterOrigin == 'all' ? null : _filterOrigin,
      search:  _searchCtrl.text.trim().isEmpty ? null : _searchCtrl.text.trim(),
      agentId: _filterAgentId,
      page:    _page,
    );
    if (!mounted) return;
    setState(() {
      _loadingMore = false;
      if (r is Success<CrmLeadPage>) {
        _leads.addAll(r.data.leads);
        _lastPage = r.data.lastPage;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text('CRM', style: TextStyle(color: _kText, fontWeight: FontWeight.bold)),
        actions: [
          AnimatedBuilder(
            animation: _tab,
            builder: (_, __) => _tab.index == 0
                ? IconButton(
                    icon: const Icon(Icons.refresh_rounded, color: _kSubtext),
                    onPressed: () => _load(reset: true),
                  )
                : const SizedBox(),
          ),
        ],
        bottom: TabBar(
          controller: _tab,
          indicatorColor: _kGold,
          labelColor: _kGold,
          unselectedLabelColor: _kSubtext,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          unselectedLabelStyle: const TextStyle(fontSize: 13),
          tabs: const [
            Tab(text: 'Leads'),
            Tab(text: 'Pipeline'),
            Tab(text: 'Agenda'),
            Tab(text: 'Analytics'),
          ],
        ),
      ),
      floatingActionButton: AnimatedBuilder(
        animation: _tab,
        builder: (_, __) => _tab.index == 0
            ? FloatingActionButton(
                backgroundColor: _kGold,
                foregroundColor: Colors.black,
                child: const Icon(Icons.person_add_rounded),
                onPressed: () async {
                  final created = await showCreateLeadSheet(context);
                  if (created == true) _load(reset: true);
                },
              )
            : const SizedBox(),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          // ── Tab 1: Leads list ──────────────────────────────────
          Column(
            children: [
              _SearchBar(
                controller: _searchCtrl,
                onSubmitted: (_) => _load(reset: true),
              ),
              if (_todayAppts.isNotEmpty || _overdueReminders.isNotEmpty)
                _TodaySummaryBanner(
                  appointments: _todayAppts,
                  overdueReminders: _overdueReminders,
                  onTapAgenda: () => _tab.animateTo(2),
                ),
              if (_stats != null) _StatsRow(stats: _stats!),
              _FilterBar(
                statusOptions: _statusOptions,
                originOptions: _originOptions,
                filterStatus: _filterStatus,
                filterOrigin: _filterOrigin,
                onStatusChanged: (v) { setState(() => _filterStatus = v); _load(reset: true); },
                onOriginChanged: (v) { setState(() => _filterOrigin = v); _load(reset: true); },
                agents: _agents,
                filterAgentId: _filterAgentId,
                onAgentChanged: (v) { setState(() => _filterAgentId = v); _load(reset: true); },
              ),
              Expanded(child: _buildBody()),
            ],
          ),
          // ── Tab 2: Pipeline Kanban ─────────────────────────────
          CrmPipelinePage(user: widget.user),
          // ── Tab 3: Agenda ──────────────────────────────────────
          CrmAgendaPage(user: widget.user),
          // ── Tab 4: Analytics ───────────────────────────────────
          CrmAnalyticsPage(user: widget.user),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_result == null && _leads.isEmpty) {
      return const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)));
    }
    if (_result is AppError<CrmLeadPage> && _leads.isEmpty) {
      return ErrorState(
        message: (_result as AppError<CrmLeadPage>).message,
        onRetry: () => _load(reset: true),
      );
    }
    if (_leads.isEmpty) {
      return const EmptyState(message: 'No hay leads en el CRM', icon: Icons.people_outline_rounded);
    }
    return NotificationListener<ScrollNotification>(
      onNotification: (n) {
        if (n.metrics.pixels >= n.metrics.maxScrollExtent - 200) _loadMore();
        return false;
      },
      child: RefreshIndicator(
        color: _kGold,
        backgroundColor: _kSurface,
        onRefresh: () => _load(reset: true),
        child: ListView.builder(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
          itemCount: _leads.length + (_loadingMore ? 1 : 0),
          itemBuilder: (context, i) {
            if (i == _leads.length) {
              return const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold))),
              );
            }
            return _LeadCard(
              lead: _leads[i],
              onTap: () async {
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => CrmLeadDetailPage(leadId: _leads[i].id, user: widget.user)),
                );
                _load(reset: true);
              },
            );
          },
        ),
      ),
    );
  }
}

// ─── Search bar ───────────────────────────────────────────────────────────────

class _SearchBar extends StatelessWidget {
  final TextEditingController controller;
  final ValueChanged<String> onSubmitted;
  const _SearchBar({required this.controller, required this.onSubmitted});

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
        child: TextField(
          controller: controller,
          style: const TextStyle(color: _kText, fontSize: 14),
          decoration: InputDecoration(
            hintText: 'Buscar por nombre, teléfono...',
            hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
            prefixIcon: const Icon(Icons.search_rounded, color: _kSubtext, size: 20),
            suffixIcon: ValueListenableBuilder(
              valueListenable: controller,
              builder: (_, v, __) => v.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, color: _kSubtext, size: 18),
                      onPressed: () {
                        controller.clear();
                        onSubmitted('');
                      },
                    )
                  : const SizedBox(),
            ),
            filled: true,
            fillColor: _kSurface,
            contentPadding: const EdgeInsets.symmetric(vertical: 10),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
          ),
          onSubmitted: onSubmitted,
          textInputAction: TextInputAction.search,
        ),
      );
}

// ─── Stats row ────────────────────────────────────────────────────────────────

class _StatsRow extends StatelessWidget {
  final CrmStats stats;
  const _StatsRow({required this.stats});

  @override
  Widget build(BuildContext context) => SizedBox(
        height: 70,
        child: ListView(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          scrollDirection: Axis.horizontal,
          children: [
            _StatChip(label: 'Total',     value: '${stats.total}',         color: _kGold),
            _StatChip(label: 'Nuevos',    value: '${stats.newLeads}',       color: const Color(0xFF3498DB)),
            _StatChip(label: 'Activos',   value: '${stats.active}',         color: const Color(0xFF2E7D32)),
            _StatChip(label: 'Ganados',   value: '${stats.won}',            color: const Color(0xFF27AE60)),
            _StatChip(label: 'Eventos',   value: '${stats.fromEvents}',     color: const Color(0xFFE67E22)),
            _StatChip(label: 'Seguimiento', value: '${stats.needsFollowUp}', color: const Color(0xFFC62828)),
          ],
        ),
      );
}

class _StatChip extends StatelessWidget {
  final String label;
  final String value;
  final Color color;
  const _StatChip({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(value, style: TextStyle(color: color, fontSize: 16, fontWeight: FontWeight.bold)),
            Text(label, style: const TextStyle(color: _kSubtext, fontSize: 10)),
          ],
        ),
      );
}

// ─── Filter bar ───────────────────────────────────────────────────────────────

class _FilterBar extends StatelessWidget {
  final List<(String, String)> statusOptions;
  final List<(String, String)> originOptions;
  final String filterStatus;
  final String filterOrigin;
  final ValueChanged<String> onStatusChanged;
  final ValueChanged<String> onOriginChanged;
  final List<CrmAgentModel> agents;
  final int? filterAgentId;
  final ValueChanged<int?> onAgentChanged;

  const _FilterBar({
    required this.statusOptions,
    required this.originOptions,
    required this.filterStatus,
    required this.filterOrigin,
    required this.onStatusChanged,
    required this.onOriginChanged,
    this.agents = const [],
    this.filterAgentId,
    required this.onAgentChanged,
  });

  static const _statusColors = {
    'new':         Color(0xFF3498DB),
    'contacted':   Color(0xFF9B59B6),
    'qualified':   Color(0xFF1ABC9C),
    'proposal':    Color(0xFFE67E22),
    'negotiation': Color(0xFFF39C12),
    'won':         Color(0xFF27AE60),
    'lost':        Color(0xFFE74C3C),
    'all':         _kGold,
  };

  @override
  Widget build(BuildContext context) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status filters
          SizedBox(
            height: 38,
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              scrollDirection: Axis.horizontal,
              children: statusOptions.map((f) {
                final sel = filterStatus == f.$1;
                final col = _statusColors[f.$1] ?? _kSubtext;
                return GestureDetector(
                  onTap: () => onStatusChanged(f.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: sel ? col.withOpacity(0.2) : _kSurface,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: sel ? col : Colors.white12),
                    ),
                    child: Text(f.$2,
                        style: TextStyle(
                          color: sel ? col : _kSubtext,
                          fontSize: 12,
                          fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                        )),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 6),
          // Origin filters
          SizedBox(
            height: 34,
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              scrollDirection: Axis.horizontal,
              children: originOptions.map((f) {
                final sel = filterOrigin == f.$1;
                return GestureDetector(
                  onTap: () => onOriginChanged(f.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: sel ? _kGold.withOpacity(0.12) : _kSurface,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: sel ? _kGold : Colors.white12),
                    ),
                    child: Text(f.$2,
                        style: TextStyle(
                          color: sel ? _kGold : _kSubtext,
                          fontSize: 11,
                          fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                        )),
                  ),
                );
              }).toList(),
            ),
          ),
          // Agent filter (admins only, when agents are loaded)
          if (agents.isNotEmpty) ...[
            const SizedBox(height: 4),
            SizedBox(
              height: 34,
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                scrollDirection: Axis.horizontal,
                children: [
                  GestureDetector(
                    onTap: () => onAgentChanged(null),
                    child: Container(
                      margin: const EdgeInsets.only(right: 6),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: filterAgentId == null ? const Color(0xFF9B59B6).withOpacity(0.15) : _kSurface,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                            color: filterAgentId == null ? const Color(0xFF9B59B6) : Colors.white12),
                      ),
                      child: Row(mainAxisSize: MainAxisSize.min, children: [
                        const Icon(Icons.people_alt_rounded, size: 12,
                            color: Color(0xFF9B59B6)),
                        const SizedBox(width: 4),
                        Text('Todos',
                            style: TextStyle(
                              color: filterAgentId == null ? const Color(0xFF9B59B6) : _kSubtext,
                              fontSize: 11,
                              fontWeight: filterAgentId == null ? FontWeight.bold : FontWeight.normal,
                            )),
                      ]),
                    ),
                  ),
                  ...agents.map((a) {
                    final sel = filterAgentId == a.id;
                    return GestureDetector(
                      onTap: () => onAgentChanged(a.id),
                      child: Container(
                        margin: const EdgeInsets.only(right: 6),
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: sel ? const Color(0xFF9B59B6).withOpacity(0.15) : _kSurface,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                              color: sel ? const Color(0xFF9B59B6) : Colors.white12),
                        ),
                        child: Text(a.name,
                            style: TextStyle(
                              color: sel ? const Color(0xFF9B59B6) : _kSubtext,
                              fontSize: 11,
                              fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                            )),
                      ),
                    );
                  }),
                ],
              ),
            ),
          ],
          const SizedBox(height: 6),
        ],
      );
}

// ─── Lead card ────────────────────────────────────────────────────────────────

class _LeadCard extends StatelessWidget {
  final CrmLead lead;
  final VoidCallback onTap;
  const _LeadCard({required this.lead, required this.onTap});

  static const _statusColors = {
    'new':         Color(0xFF3498DB),
    'contacted':   Color(0xFF9B59B6),
    'qualified':   Color(0xFF1ABC9C),
    'proposal':    Color(0xFFE67E22),
    'negotiation': Color(0xFFF39C12),
    'won':         Color(0xFF27AE60),
    'lost':        Color(0xFFE74C3C),
  };

  static const _priorityColors = {
    'urgent': Color(0xFFC62828),
    'high':   Color(0xFFE65100),
    'medium': Color(0xFFF57F17),
    'low':    Color(0xFF2E7D32),
  };

  Color get _statusColor => _statusColors[lead.status] ?? _kSubtext;
  Color get _priorityColor => _priorityColors[lead.priority] ?? _kSubtext;

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
          border: Border.all(color: _statusColor.withOpacity(0.25)),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
              child: Text(lead.name,
                  style: const TextStyle(color: _kText, fontSize: 15, fontWeight: FontWeight.bold)),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: _statusColor.withOpacity(0.15),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(lead.statusLabel,
                  style: TextStyle(color: _statusColor, fontSize: 11, fontWeight: FontWeight.bold)),
            ),
          ]),
          const SizedBox(height: 6),
          Row(children: [
            const Icon(Icons.phone_outlined, size: 13, color: _kSubtext),
            const SizedBox(width: 4),
            Text(lead.phone, style: const TextStyle(color: _kSubtext, fontSize: 13)),
            if (lead.email != null) ...[
              const SizedBox(width: 10),
              const Icon(Icons.email_outlined, size: 13, color: _kSubtext),
              const SizedBox(width: 4),
              Expanded(
                child: Text(lead.email!,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: _kSubtext, fontSize: 12)),
              ),
            ],
          ]),
          if (lead.vehicle != null) ...[
            const SizedBox(height: 4),
            Row(children: [
              const Icon(Icons.directions_car_outlined, size: 13, color: _kSubtext),
              const SizedBox(width: 4),
              Expanded(
                child: Text(lead.vehicle!.name,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: _kSubtext, fontSize: 12)),
              ),
            ]),
          ],
          const SizedBox(height: 8),
          Row(children: [
            // Priority dot
            Container(
              width: 8, height: 8,
              decoration: BoxDecoration(color: _priorityColor, shape: BoxShape.circle),
            ),
            const SizedBox(width: 5),
            Text(_priorityLabel(lead.priority),
                style: TextStyle(color: _priorityColor, fontSize: 11)),
            const Spacer(),
            if (lead.sourceLabel.isNotEmpty)
              Text(lead.sourceLabel,
                  style: const TextStyle(color: _kSubtext, fontSize: 11)),
            const SizedBox(width: 8),
            Text(_formatDate(lead.createdAt),
                style: const TextStyle(color: _kSubtext, fontSize: 11)),
            const SizedBox(width: 4),
            const Icon(Icons.chevron_right_rounded, color: _kSubtext, size: 16),
          ]),
          // Quick contact buttons
          const SizedBox(height: 8),
          Row(children: [
            _QuickBtn(
              icon: Icons.phone_rounded,
              label: 'Llamar',
              color: const Color(0xFF2ECC71),
              onTap: () => _launch('tel:${lead.phone}'),
            ),
            const SizedBox(width: 8),
            if (lead.whatsapp != null || lead.phone.isNotEmpty)
              _QuickBtn(
                icon: Icons.chat_rounded,
                label: 'WhatsApp',
                color: const Color(0xFF25D366),
                onTap: () {
                  final num = (lead.whatsapp ?? lead.phone).replaceAll(RegExp(r'[^0-9+]'), '');
                  _launch('https://wa.me/$num');
                },
              ),
          ]),
        ]),
      ),
    );
  }

  Future<void> _launch(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      Fluttertoast.showToast(msg: 'No se pudo abrir la app', backgroundColor: Colors.red[700]);
    }
  }

  String _priorityLabel(String p) => switch (p) {
    'urgent' => 'Urgente',
    'high'   => 'Alta',
    'medium' => 'Media',
    'low'    => 'Baja',
    _        => p,
  };

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}';
    } catch (_) {
      return '';
    }
  }
}

// ─── Quick contact button ─────────────────────────────────────────────────────

class _QuickBtn extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _QuickBtn({required this.icon, required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) => GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: color.withOpacity(0.35)),
          ),
          child: Row(mainAxisSize: MainAxisSize.min, children: [
            Icon(icon, size: 12, color: color),
            const SizedBox(width: 4),
            Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
          ]),
        ),
      );
}

// ─── Today summary banner ─────────────────────────────────────────────────────

class _TodaySummaryBanner extends StatelessWidget {
  final List<AppointmentModel> appointments;
  final List<ReminderModel> overdueReminders;
  final VoidCallback onTapAgenda;

  const _TodaySummaryBanner({
    required this.appointments,
    required this.overdueReminders,
    required this.onTapAgenda,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTapAgenda,
      child: Container(
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 4),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: overdueReminders.isNotEmpty
                ? const Color(0xFFE74C3C).withOpacity(0.4)
                : _kGold.withOpacity(0.3),
          ),
        ),
        child: Row(children: [
          Icon(
            overdueReminders.isNotEmpty ? Icons.warning_amber_rounded : Icons.today_rounded,
            size: 18,
            color: overdueReminders.isNotEmpty ? const Color(0xFFE74C3C) : _kGold,
          ),
          const SizedBox(width: 10),
          Expanded(child: Wrap(spacing: 12, children: [
            if (appointments.isNotEmpty)
              Text('${appointments.length} cita${appointments.length > 1 ? 's' : ''} hoy',
                  style: const TextStyle(color: _kText, fontSize: 12, fontWeight: FontWeight.bold)),
            if (overdueReminders.isNotEmpty)
              Text('${overdueReminders.length} recordatorio${overdueReminders.length > 1 ? 's' : ''} vencido${overdueReminders.length > 1 ? 's' : ''}',
                  style: const TextStyle(color: Color(0xFFE74C3C), fontSize: 12, fontWeight: FontWeight.bold)),
          ])),
          const Icon(Icons.chevron_right_rounded, color: _kSubtext, size: 16),
        ]),
      ),
    );
  }
}
