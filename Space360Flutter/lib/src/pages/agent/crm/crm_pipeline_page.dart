import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_lead_detail_page.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kSurface = Color(0xFF1A1A1A);
const _kCard    = Color(0xFF222222);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

const _kStatuses = [
  ('new',         'Nuevos',      Color(0xFF3498DB)),
  ('contacted',   'Contactado',  Color(0xFF9B59B6)),
  ('qualified',   'Calificado',  Color(0xFF1ABC9C)),
  ('proposal',    'Propuesta',   Color(0xFFE67E22)),
  ('negotiation', 'Negociación', Color(0xFFF39C12)),
  ('won',         'Ganado',      Color(0xFF27AE60)),
  ('lost',        'Perdido',     Color(0xFFE74C3C)),
];

class CrmPipelinePage extends StatefulWidget {
  final AuthUser user;
  const CrmPipelinePage({super.key, required this.user});

  @override
  State<CrmPipelinePage> createState() => _CrmPipelinePageState();
}

class _CrmPipelinePageState extends State<CrmPipelinePage>
    with AutomaticKeepAliveClientMixin {
  final _service    = CrmService();
  final _searchCtrl = TextEditingController();

  Map<String, List<CrmLead>>? _pipeline;
  bool _loading = true;
  String? _error;
  String _query = '';

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final futures = _kStatuses
        .map((s) => _service.getLeads(status: s.$1, page: 1, perPage: 200))
        .toList();
    final results = await Future.wait(futures);
    if (!mounted) return;

    final map = <String, List<CrmLead>>{};
    bool hasError = false;
    for (int i = 0; i < _kStatuses.length; i++) {
      final r = results[i];
      if (r is Success<CrmLeadPage>) {
        map[_kStatuses[i].$1] = r.data.leads;
      } else {
        map[_kStatuses[i].$1] = [];
        hasError = true;
      }
    }
    setState(() {
      _pipeline = map;
      _loading  = false;
      if (hasError) _error = 'Algunos estados no cargaron';
    });
  }

  List<CrmLead> _filter(List<CrmLead> leads) {
    if (_query.isEmpty) return leads;
    final q = _query.toLowerCase();
    return leads.where((l) =>
      l.name.toLowerCase().contains(q) ||
      l.phone.contains(q) ||
      (l.email?.toLowerCase().contains(q) ?? false)
    ).toList();
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)),
      );
    }
    return Column(
      children: [
        // ── Search bar ─────────────────────────────────────────
        Padding(
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 4),
          child: TextField(
            controller: _searchCtrl,
            style: const TextStyle(color: _kText, fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Filtrar por nombre, teléfono...',
              hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
              prefixIcon: const Icon(Icons.search_rounded, color: _kSubtext, size: 18),
              suffixIcon: ValueListenableBuilder(
                valueListenable: _searchCtrl,
                builder: (_, v, __) => v.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, color: _kSubtext, size: 16),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() => _query = '');
                        },
                      )
                    : const SizedBox(),
              ),
              filled: true,
              fillColor: _kSurface,
              contentPadding: const EdgeInsets.symmetric(vertical: 8),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide.none,
              ),
            ),
            onChanged: (v) => setState(() => _query = v.trim()),
          ),
        ),
        if (_error != null)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
            child: Text(_error!, style: const TextStyle(color: Colors.orange, fontSize: 11)),
          ),
        // ── Kanban board ───────────────────────────────────────
        Expanded(
          child: RefreshIndicator(
            color: _kGold,
            backgroundColor: _kSurface,
            onRefresh: _load,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(12, 4, 12, 24),
              children: _kStatuses.map((s) {
                final leads = _filter(_pipeline?[s.$1] ?? []);
                return _KanbanColumn(
                  label:     s.$2,
                  color:     s.$3,
                  leads:     leads,
                  onLeadTap: (lead) async {
                    await Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => CrmLeadDetailPage(leadId: lead.id, user: widget.user),
                      ),
                    );
                    _load();
                  },
                );
              }).toList(),
            ),
          ),
        ),
      ],
    );
  }
}

// ─── Kanban column ────────────────────────────────────────────────────────────

class _KanbanColumn extends StatelessWidget {
  final String label;
  final Color color;
  final List<CrmLead> leads;
  final void Function(CrmLead) onLeadTap;

  const _KanbanColumn({
    required this.label,
    required this.color,
    required this.leads,
    required this.onLeadTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 200,
      margin: const EdgeInsets.only(right: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.15),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              border: Border.all(color: color.withOpacity(0.4)),
            ),
            child: Row(children: [
              Expanded(
                child: Text(label,
                    style: TextStyle(color: color, fontSize: 13, fontWeight: FontWeight.bold)),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text('${leads.length}',
                    style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
              ),
            ]),
          ),
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: _kSurface,
                border: Border(
                  left:   BorderSide(color: color.withOpacity(0.3)),
                  right:  const BorderSide(color: Colors.white10),
                  bottom: const BorderSide(color: Colors.white10),
                ),
                borderRadius: const BorderRadius.vertical(bottom: Radius.circular(12)),
              ),
              child: leads.isEmpty
                  ? Center(
                      child: Text('Sin leads',
                          style: TextStyle(color: _kSubtext.withOpacity(0.5), fontSize: 12)),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(8),
                      itemCount: leads.length,
                      itemBuilder: (_, i) => _MiniLeadCard(
                        lead:  leads[i],
                        color: color,
                        onTap: () => onLeadTap(leads[i]),
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }
}

class _MiniLeadCard extends StatelessWidget {
  final CrmLead lead;
  final Color color;
  final VoidCallback onTap;
  const _MiniLeadCard({required this.lead, required this.color, required this.onTap});

  static const _priorityColors = {
    'urgent': Color(0xFFC62828),
    'high':   Color(0xFFE65100),
    'medium': Color(0xFFF57F17),
    'low':    Color(0xFF2E7D32),
  };

  @override
  Widget build(BuildContext context) {
    final pColor = _priorityColors[lead.priority] ?? _kSubtext;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: Colors.white.withOpacity(0.06)),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 4, offset: const Offset(0, 2))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(lead.name,
              maxLines: 1, overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.bold)),
          const SizedBox(height: 3),
          Text(lead.phone, style: const TextStyle(color: _kSubtext, fontSize: 11)),
          if (lead.vehicle != null) ...[
            const SizedBox(height: 3),
            Text(lead.vehicle!.name,
                maxLines: 1, overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: _kSubtext, fontSize: 10)),
          ],
          const SizedBox(height: 6),
          Row(children: [
            Container(width: 7, height: 7,
                decoration: BoxDecoration(color: pColor, shape: BoxShape.circle)),
            const Spacer(),
            Text(_fmtDate(lead.createdAt), style: const TextStyle(color: _kSubtext, fontSize: 10)),
          ]),
        ]),
      ),
    );
  }

  String _fmtDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}';
    } catch (_) { return ''; }
  }
}
