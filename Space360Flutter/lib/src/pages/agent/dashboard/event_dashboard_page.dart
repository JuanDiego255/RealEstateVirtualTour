import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/models/event_lead_model.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_lead_detail_page.dart';
import 'package:space360_flutter/src/pages/agent/dashboard/lead_detail_sheet.dart';
import 'package:space360_flutter/src/services/agent_service.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
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

class _EventDashboardPageState extends State<EventDashboardPage>
    with SingleTickerProviderStateMixin {
  late final TabController _tab;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text('Eventos', style: TextStyle(color: _kText, fontWeight: FontWeight.bold)),
        bottom: TabBar(
          controller: _tab,
          indicatorColor: _kGold,
          labelColor: _kGold,
          unselectedLabelColor: _kSubtext,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          unselectedLabelStyle: const TextStyle(fontSize: 13),
          tabs: const [
            Tab(text: 'Me interesa'),
            Tab(text: 'Cotizaciones'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          _LeadsTab(user: widget.user),
          _QuotesTab(user: widget.user),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Tab 1 — Event Leads ("Me interesa")
// ═══════════════════════════════════════════════════════════════════════════════

class _LeadsTab extends StatefulWidget {
  final AuthUser user;
  const _LeadsTab({required this.user});

  @override
  State<_LeadsTab> createState() => _LeadsTabState();
}

class _LeadsTabState extends State<_LeadsTab> with AutomaticKeepAliveClientMixin {
  final _agentService = AgentService();
  final _crmService   = CrmService();

  Resource<DashboardStats>? _stats;
  Resource<List<EventLeadModel>>? _leads;

  String _filterInterest = 'all';
  String _filterContact  = 'all';

  // Tracks event lead IDs already added to CRM
  final Set<int> _addedToCrm = {};
  // Tracks IDs currently being added (loading state)
  final Set<int> _adding = {};

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _stats = null; _leads = null; });
    final results = await Future.wait([
      _agentService.getDashboardStats(),
      _agentService.getDashboardLeads(),
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

  Future<void> _addToCrm(EventLeadModel lead) async {
    setState(() => _adding.add(lead.id));
    final r = await _crmService.addEventLeadToCrm(lead.id);
    if (!mounted) return;
    setState(() => _adding.remove(lead.id));

    if (r is Success<int>) {
      setState(() => _addedToCrm.add(lead.id));
      Fluttertoast.showToast(msg: 'Lead agregado al CRM', backgroundColor: const Color(0xFF2E7D32));
    } else if (r is AppError<int>) {
      // Check for duplicate (409) — show option to navigate to existing lead
      final msg = r.message;
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          backgroundColor: _kSurface,
          content: Text(msg, style: const TextStyle(color: _kText, fontSize: 13)),
        ));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return RefreshIndicator(
      color: _kGold,
      backgroundColor: _kSurface,
      onRefresh: _load,
      child: _stats == null
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Stats
                if (_stats is AppError<DashboardStats>)
                  ErrorState(message: (_stats as AppError<DashboardStats>).message, onRetry: _load)
                else if (_stats is Success<DashboardStats>)
                  _StatsGrid(stats: (_stats as Success<DashboardStats>).data),
                const SizedBox(height: 24),
                // Header
                Row(children: [
                  const Text('Leads', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
                  if (_leads is Success<List<EventLeadModel>>) ...[
                    const SizedBox(width: 6),
                    Text(
                      '(${_applyFilters((_leads as Success<List<EventLeadModel>>).data).length})',
                      style: const TextStyle(color: _kSubtext, fontSize: 14),
                    ),
                  ],
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.refresh_rounded, color: _kSubtext, size: 20),
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                    onPressed: _load,
                  ),
                ]),
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
      children: leads.map((l) => _EventLeadCard(
        lead: l,
        user: widget.user,
        onContacted: _load,
        isAdded:  _addedToCrm.contains(l.id),
        isAdding: _adding.contains(l.id),
        onAddToCrm: () => _addToCrm(l),
        onTap: () => showLeadDetailSheet(context, l, widget.user),
      )).toList(),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Tab 2 — Vehicle Quotes ("Cotizaciones")
// ═══════════════════════════════════════════════════════════════════════════════

class _QuotesTab extends StatefulWidget {
  final AuthUser user;
  const _QuotesTab({required this.user});

  @override
  State<_QuotesTab> createState() => _QuotesTabState();
}

class _QuotesTabState extends State<_QuotesTab> with AutomaticKeepAliveClientMixin {
  final _crmService = CrmService();

  List<VehicleQuoteModel> _quotes = [];
  bool _loading = true;
  String? _error;
  int _page = 1;
  int _lastPage = 1;
  bool _loadingMore = false;

  final Set<int> _addedToCrm = {};
  final Set<int> _adding     = {};

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load(reset: true);
  }

  Future<void> _load({bool reset = false}) async {
    if (reset) {
      _page = 1;
      _quotes.clear();
    }
    setState(() { _loading = true; _error = null; });
    final r = await _crmService.getQuotes(page: _page);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (r is Success<List<VehicleQuoteModel>>) {
        if (reset) _quotes.clear();
        _quotes.addAll(r.data);
      } else if (r is AppError<List<VehicleQuoteModel>>) {
        _error = r.message;
      }
    });
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    _page++;
    final r = await _crmService.getQuotes(page: _page);
    if (!mounted) return;
    setState(() {
      _loadingMore = false;
      if (r is Success<List<VehicleQuoteModel>>) _quotes.addAll(r.data);
    });
  }

  Future<void> _addToCrm(VehicleQuoteModel quote) async {
    setState(() => _adding.add(quote.id));
    final r = await _crmService.addQuoteToCrm(quote.id);
    if (!mounted) return;
    setState(() => _adding.remove(quote.id));

    if (r is Success<int>) {
      setState(() => _addedToCrm.add(quote.id));
      Fluttertoast.showToast(msg: 'Cotización agregada al CRM', backgroundColor: const Color(0xFF2E7D32));
      // Navigate to the new lead detail
      if (mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => CrmLeadDetailPage(leadId: r.data, user: widget.user),
          ),
        );
      }
    } else if (r is AppError<int>) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        backgroundColor: _kSurface,
        content: Text(r.message, style: const TextStyle(color: _kText, fontSize: 13)),
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    if (_loading && _quotes.isEmpty) {
      return const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)));
    }
    if (_error != null && _quotes.isEmpty) {
      return ErrorState(message: _error!, onRetry: () => _load(reset: true));
    }
    if (_quotes.isEmpty) {
      return const EmptyState(message: 'No hay cotizaciones registradas', icon: Icons.calculate_outlined);
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
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
          itemCount: _quotes.length + (_loadingMore ? 1 : 0),
          itemBuilder: (context, i) {
            if (i == _quotes.length) {
              return const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold))),
              );
            }
            final q = _quotes[i];
            return _QuoteCard(
              quote: q,
              isAdded:  _addedToCrm.contains(q.id),
              isAdding: _adding.contains(q.id),
              onAddToCrm: () => _addToCrm(q),
            );
          },
        ),
      ),
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
          _StatCard(label: 'Total leads',    value: '${stats.totalLeads}',   icon: Icons.people_rounded,                color: _kGold),
          _StatCard(label: 'Hoy',            value: '${stats.leadsToday}',   icon: Icons.today_rounded,                 color: const Color(0xFF3498DB)),
          _StatCard(label: 'Hot',            value: '${stats.leadsHot}',     icon: Icons.local_fire_department_rounded, color: const Color(0xFFC62828)),
          _StatCard(label: 'Sin contactar',  value: '${stats.leadsPending}', icon: Icons.mark_email_unread_outlined,    color: const Color(0xFFE67E22)),
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
    ('all', 'Todos'), ('hot', '🔥 Hot'), ('high', 'Alto'), ('medium', 'Medio'), ('low', 'Bajo'),
  ];
  static const _contactFilters = [
    ('all', 'Todos'), ('pending', 'Sin contactar'), ('contacted', 'Contactados'),
  ];
  static const _interestColors = {
    'hot': Color(0xFFC62828), 'high': Color(0xFFE65100),
    'medium': Color(0xFFF57F17), 'low': Color(0xFF2E7D32), 'all': _kGold,
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
                    child: Text(f.$2, style: TextStyle(color: sel ? col : _kSubtext,
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
                  child: Text(f.$2, style: TextStyle(color: sel ? _kGold : _kSubtext,
                      fontSize: 11, fontWeight: sel ? FontWeight.bold : FontWeight.normal)),
                ),
              );
            }).toList(),
          ),
        ],
      );
}

// ─── Event lead card ──────────────────────────────────────────────────────────

class _EventLeadCard extends StatelessWidget {
  final EventLeadModel lead;
  final AuthUser user;
  final VoidCallback onContacted;
  final bool isAdded;
  final bool isAdding;
  final VoidCallback onAddToCrm;
  final VoidCallback onTap;

  const _EventLeadCard({
    required this.lead,
    required this.user,
    required this.onContacted,
    required this.isAdded,
    required this.isAdding,
    required this.onAddToCrm,
    required this.onTap,
  });

  static const _iColors = {
    'hot': Color(0xFFC62828), 'high': Color(0xFFE65100),
    'medium': Color(0xFFF57F17), 'low': Color(0xFF2E7D32),
  };
  static const _iLabels = {
    'hot': '🔥 Hot', 'high': 'Alto', 'medium': 'Medio', 'low': 'Bajo',
  };

  Color get _iColor => _iColors[lead.interestLevel] ?? _kSubtext;

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
              child: Text(_iLabels[lead.interestLevel] ?? lead.interestLevel,
                  style: TextStyle(color: _iColor, fontSize: 11, fontWeight: FontWeight.bold)),
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
              Text(lead.vehicle!.name, style: const TextStyle(color: _kSubtext, fontSize: 12)),
            ]),
          ],
          const SizedBox(height: 8),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text(_formatDate(lead.createdAt),
                style: const TextStyle(color: _kSubtext, fontSize: 11)),
            Row(children: [
              // Agregar al CRM button
              _AddToCrmButton(isAdded: isAdded, isAdding: isAdding, onTap: onAddToCrm),
              const SizedBox(width: 6),
              // Contacted state
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
        ]),
      ),
    );
  }

  Future<void> _markContacted(BuildContext context) async {
    final result = await AgentService().markLeadContacted(lead.id);
    if (result is Success<bool>) {
      Fluttertoast.showToast(msg: 'Marcado como contactado', backgroundColor: const Color(0xFF2E7D32));
      onContacted();
    } else if (result is AppError) {
      Fluttertoast.showToast(msg: (result as AppError<bool>).message, backgroundColor: Colors.red[700]);
    }
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2,'0')}/${dt.month.toString().padLeft(2,'0')} '
          '${dt.hour.toString().padLeft(2,'0')}:${dt.minute.toString().padLeft(2,'0')}';
    } catch (_) { return iso; }
  }
}

// ─── Quote card ───────────────────────────────────────────────────────────────

class _QuoteCard extends StatelessWidget {
  final VehicleQuoteModel quote;
  final bool isAdded;
  final bool isAdding;
  final VoidCallback onAddToCrm;

  const _QuoteCard({
    required this.quote,
    required this.isAdded,
    required this.isAdding,
    required this.onAddToCrm,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _kGold.withOpacity(0.2)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Expanded(
            child: Text(quote.customerDisplay,
                style: const TextStyle(color: _kText, fontSize: 15, fontWeight: FontWeight.bold)),
          ),
          Text(quote.monthlyFormatted,
              style: const TextStyle(color: _kGold, fontSize: 14, fontWeight: FontWeight.bold)),
        ]),
        const SizedBox(height: 4),
        Text('/mes', style: const TextStyle(color: _kSubtext, fontSize: 11)),
        const SizedBox(height: 6),
        if (quote.vehicle != null)
          Row(children: [
            const Icon(Icons.directions_car_outlined, size: 13, color: _kSubtext),
            const SizedBox(width: 4),
            Expanded(
              child: Text(quote.vehicle!.name,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: _kSubtext, fontSize: 12)),
            ),
          ]),
        if (quote.customerPhone != null) ...[
          const SizedBox(height: 4),
          Row(children: [
            const Icon(Icons.phone_outlined, size: 13, color: _kSubtext),
            const SizedBox(width: 4),
            Text(quote.customerPhone!, style: const TextStyle(color: _kSubtext, fontSize: 12)),
          ]),
        ],
        const SizedBox(height: 6),
        Row(children: [
          _InfoPill('Precio: ${quote.vehiclePriceFormatted}'),
          const SizedBox(width: 6),
          if (quote.termMonths != null) _InfoPill('${quote.termMonths} meses'),
        ]),
        const SizedBox(height: 10),
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Text(_formatDate(quote.createdAt),
              style: const TextStyle(color: _kSubtext, fontSize: 11)),
          _AddToCrmButton(isAdded: isAdded, isAdding: isAdding, onTap: onAddToCrm),
        ]),
      ]),
    );
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2,'0')}/${dt.month.toString().padLeft(2,'0')}/${dt.year}';
    } catch (_) { return iso; }
  }
}

class _InfoPill extends StatelessWidget {
  final String text;
  const _InfoPill(this.text);
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.05),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(text, style: const TextStyle(color: _kSubtext, fontSize: 11)),
      );
}

// ─── Shared: Add to CRM button ────────────────────────────────────────────────

class _AddToCrmButton extends StatelessWidget {
  final bool isAdded;
  final bool isAdding;
  final VoidCallback onTap;
  const _AddToCrmButton({required this.isAdded, required this.isAdding, required this.onTap});

  @override
  Widget build(BuildContext context) {
    if (isAdded) {
      return Row(children: const [
        Icon(Icons.check_circle_rounded, color: Color(0xFF27AE60), size: 14),
        SizedBox(width: 4),
        Text('En CRM', style: TextStyle(color: Color(0xFF27AE60), fontSize: 11, fontWeight: FontWeight.bold)),
      ]);
    }
    if (isAdding) {
      return const SizedBox(
        width: 18, height: 18,
        child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(_kGold)),
      );
    }
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
        decoration: BoxDecoration(
          color: const Color(0xFF27AE60).withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFF27AE60).withOpacity(0.4)),
        ),
        child: Row(children: const [
          Icon(Icons.add_rounded, size: 12, color: Color(0xFF27AE60)),
          SizedBox(width: 3),
          Text('Al CRM', style: TextStyle(color: Color(0xFF27AE60), fontSize: 11, fontWeight: FontWeight.bold)),
        ]),
      ),
    );
  }
}
