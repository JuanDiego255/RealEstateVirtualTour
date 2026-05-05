import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_lead_detail_page.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
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

class _CrmPipelinePageState extends State<CrmPipelinePage> {
  final _service = CrmService();
  Map<String, List<CrmLead>>? _pipeline;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });

    // Load all leads (all statuses), up to 200
    final futures = <Future<Resource<CrmLeadPage>>>[];
    for (final s in _kStatuses) {
      futures.add(_service.getLeads(status: s.$1, page: 1));
    }
    final results = await Future.wait(futures);
    if (!mounted) return;

    final map = <String, List<CrmLead>>{};
    bool hasError = false;
    for (int i = 0; i < _kStatuses.length; i++) {
      final status = _kStatuses[i].$1;
      final r = results[i];
      if (r is Success<CrmLeadPage>) {
        map[status] = r.data.leads;
      } else {
        map[status] = [];
        hasError = true;
      }
    }
    setState(() {
      _pipeline = map;
      _loading  = false;
      if (hasError) _error = 'Algunos estados no cargaron';
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)),
      );
    }
    return RefreshIndicator(
      color: _kGold,
      backgroundColor: _kSurface,
      onRefresh: _load,
      child: Column(
        children: [
          if (_error != null)
            Padding(
              padding: const EdgeInsets.all(8),
              child: Text(_error!, style: const TextStyle(color: Colors.orange, fontSize: 12)),
            ),
          Expanded(
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(12, 12, 12, 24),
              children: _kStatuses.map((s) => _KanbanColumn(
                status: s.$1,
                label:  s.$2,
                color:  s.$3,
                leads:  _pipeline?[s.$1] ?? [],
                user:   widget.user,
                onLeadTap: (lead) async {
                  await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => CrmLeadDetailPage(leadId: lead.id, user: widget.user),
                    ),
                  );
                  _load();
                },
              )).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Kanban column ────────────────────────────────────────────────────────────

class _KanbanColumn extends StatelessWidget {
  final String status;
  final String label;
  final Color color;
  final List<CrmLead> leads;
  final AuthUser user;
  final void Function(CrmLead) onLeadTap;

  const _KanbanColumn({
    required this.status,
    required this.label,
    required this.color,
    required this.leads,
    required this.user,
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
          // Column header
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
                    style: TextStyle(
                      color: color,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    )),
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
          // Cards
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: _kSurface,
                border: Border(
                  left:  BorderSide(color: color.withOpacity(0.3)),
                  right: BorderSide(color: Colors.white10),
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
                      itemBuilder: (context, i) => _MiniLeadCard(
                        lead: leads[i],
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
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.bold)),
          const SizedBox(height: 3),
          Text(lead.phone, style: const TextStyle(color: _kSubtext, fontSize: 11)),
          if (lead.vehicle != null) ...[
            const SizedBox(height: 3),
            Text(lead.vehicle!.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: _kSubtext, fontSize: 10)),
          ],
          const SizedBox(height: 6),
          Row(children: [
            Container(
              width: 7, height: 7,
              decoration: BoxDecoration(color: pColor, shape: BoxShape.circle),
            ),
            const Spacer(),
            Text(_formatDate(lead.createdAt),
                style: const TextStyle(color: _kSubtext, fontSize: 10)),
          ]),
        ]),
      ),
    );
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}';
    } catch (_) {
      return '';
    }
  }
}
