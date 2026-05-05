import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kCard    = Color(0xFF222222);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

const _statusColors = {
  'new':         Color(0xFF3498DB),
  'contacted':   Color(0xFF9B59B6),
  'qualified':   Color(0xFF1ABC9C),
  'proposal':    Color(0xFFE67E22),
  'negotiation': Color(0xFFF39C12),
  'won':         Color(0xFF27AE60),
  'lost':        Color(0xFFE74C3C),
};

const _statusLabels = {
  'new':         'Nuevo',
  'contacted':   'Contactado',
  'qualified':   'Calificado',
  'proposal':    'Propuesta',
  'negotiation': 'Negociación',
  'won':         'Ganado',
  'lost':        'Perdido',
};

const _activityIcons = {
  'call':          Icons.phone_rounded,
  'email':         Icons.email_rounded,
  'whatsapp':      Icons.chat_rounded,
  'visit':         Icons.location_on_rounded,
  'meeting':       Icons.groups_rounded,
  'note':          Icons.note_rounded,
  'status_change': Icons.swap_horiz_rounded,
  'other':         Icons.radio_button_unchecked_rounded,
};

class CrmLeadDetailPage extends StatefulWidget {
  final int leadId;
  final AuthUser user;
  const CrmLeadDetailPage({super.key, required this.leadId, required this.user});

  @override
  State<CrmLeadDetailPage> createState() => _CrmLeadDetailPageState();
}

class _CrmLeadDetailPageState extends State<CrmLeadDetailPage> {
  final _service = CrmService();
  CrmLead? _lead;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final r = await _service.getLead(widget.leadId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (r is Success<CrmLead>) {
        _lead = r.data;
      } else if (r is AppError<CrmLead>) {
        _error = r.message;
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
        iconTheme: const IconThemeData(color: _kText),
        title: Text(
          _lead?.name ?? 'Lead',
          style: const TextStyle(color: _kText, fontWeight: FontWeight.bold, fontSize: 17),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _kSubtext),
            onPressed: _load,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)))
          : _error != null
              ? Center(
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    Text(_error!, style: const TextStyle(color: _kSubtext)),
                    const SizedBox(height: 12),
                    TextButton(onPressed: _load, child: const Text('Reintentar', style: TextStyle(color: _kGold))),
                  ]),
                )
              : _buildContent(),
      bottomNavigationBar: _lead == null
          ? null
          : _ActionBar(
              lead: _lead!,
              onStatusChanged: _load,
              onActivityLogged: _load,
            ),
    );
  }

  Widget _buildContent() {
    final lead = _lead!;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
      children: [
        // ── Status badge ───────────────────────────────────────────
        _StatusBadgeRow(lead: lead),
        const SizedBox(height: 16),
        // ── Contact info ───────────────────────────────────────────
        _SectionCard(
          title: 'Contacto',
          children: [
            _InfoRow(icon: Icons.person_rounded, label: lead.name),
            _InfoRow(icon: Icons.phone_rounded, label: lead.phone),
            if (lead.email != null)
              _InfoRow(icon: Icons.email_rounded, label: lead.email!),
            if (lead.whatsapp != null)
              _InfoRow(icon: Icons.chat_rounded, label: lead.whatsapp!),
            if (lead.agent != null)
              _InfoRow(icon: Icons.badge_rounded, label: lead.agent!.name, sublabel: 'Agente'),
          ],
        ),
        const SizedBox(height: 12),
        // ── Interest info ──────────────────────────────────────────
        _SectionCard(
          title: 'Interés',
          children: [
            _InfoRow(icon: Icons.trending_up_rounded, label: _interestTypeLabel(lead.interestType), sublabel: 'Tipo'),
            _InfoRow(icon: Icons.flag_rounded, label: _priorityLabel(lead.priority), sublabel: 'Prioridad'),
            _InfoRow(icon: Icons.label_rounded, label: lead.sourceLabel, sublabel: 'Origen'),
            if (lead.budgetMin != null || lead.budgetMax != null)
              _InfoRow(
                icon: Icons.account_balance_wallet_rounded,
                label: _budgetText(lead),
                sublabel: 'Presupuesto',
              ),
          ],
        ),
        // ── Vehicle ────────────────────────────────────────────────
        if (lead.vehicle != null) ...[
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Vehículo de interés',
            children: [
              Row(children: [
                if (lead.vehicle!.image != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      lead.vehicle!.image!,
                      width: 72, height: 52, fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _vehiclePlaceholder(),
                    ),
                  )
                else
                  _vehiclePlaceholder(),
                const SizedBox(width: 12),
                Expanded(child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(lead.vehicle!.name,
                        style: const TextStyle(color: _kText, fontSize: 14, fontWeight: FontWeight.bold)),
                    if (lead.vehicle!.price != null)
                      Text(lead.vehicle!.price!,
                          style: const TextStyle(color: _kGold, fontSize: 13)),
                  ],
                )),
              ]),
            ],
          ),
        ],
        // ── Notes ──────────────────────────────────────────────────
        if (lead.notes != null && lead.notes!.isNotEmpty) ...[
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Notas',
            children: [
              Text(lead.notes!, style: const TextStyle(color: _kText, fontSize: 13, height: 1.5)),
            ],
          ),
        ],
        // ── Dates ──────────────────────────────────────────────────
        const SizedBox(height: 12),
        _SectionCard(
          title: 'Fechas',
          children: [
            _InfoRow(icon: Icons.calendar_today_rounded, label: _fmt(lead.createdAt), sublabel: 'Creado'),
            if (lead.firstContactAt != null)
              _InfoRow(icon: Icons.touch_app_rounded, label: _fmt(lead.firstContactAt!), sublabel: 'Primer contacto'),
            if (lead.lastContactAt != null)
              _InfoRow(icon: Icons.access_time_rounded, label: _fmt(lead.lastContactAt!), sublabel: 'Último contacto'),
            if (lead.nextFollowUp != null)
              _InfoRow(icon: Icons.alarm_rounded, label: _fmt(lead.nextFollowUp!), sublabel: 'Próximo seguimiento'),
          ],
        ),
        // ── Event origin ───────────────────────────────────────────
        if (lead.eventName != null) ...[
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Evento de origen',
            children: [
              _InfoRow(icon: Icons.event_rounded, label: lead.eventName!),
            ],
          ),
        ],
        // ── Activity timeline ──────────────────────────────────────
        const SizedBox(height: 20),
        const Text('Actividad', style: TextStyle(color: _kText, fontSize: 15, fontWeight: FontWeight.bold)),
        const SizedBox(height: 10),
        if (lead.activities.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(
              child: Text('Sin actividades registradas', style: TextStyle(color: _kSubtext, fontSize: 13)),
            ),
          )
        else
          ...lead.activities.map((a) => _ActivityTile(activity: a)),
      ],
    );
  }

  Widget _vehiclePlaceholder() => Container(
        width: 72, height: 52,
        decoration: BoxDecoration(color: _kCard, borderRadius: BorderRadius.circular(8)),
        child: const Center(child: Icon(Icons.directions_car_rounded, color: Color(0xFF444444), size: 22)),
      );

  String _interestTypeLabel(String t) => switch (t) {
    'buy'   => 'Compra',
    'rent'  => 'Arriendo',
    'trade' => 'Canje',
    _       => t,
  };

  String _priorityLabel(String p) => switch (p) {
    'urgent' => 'Urgente',
    'high'   => 'Alta',
    'medium' => 'Media',
    'low'    => 'Baja',
    _        => p,
  };

  String _budgetText(CrmLead lead) {
    final sym = lead.budgetCurrency == 'USD' ? '\$' : '₡';
    if (lead.budgetMin != null && lead.budgetMax != null) {
      return '$sym${_fmt2(lead.budgetMin!)} – $sym${_fmt2(lead.budgetMax!)}';
    }
    if (lead.budgetMin != null) return 'Desde $sym${_fmt2(lead.budgetMin!)}';
    return 'Hasta $sym${_fmt2(lead.budgetMax!)}';
  }

  String _fmt2(double v) {
    final s = v.toStringAsFixed(0);
    final buf = StringBuffer();
    int c = 0;
    for (int i = s.length - 1; i >= 0; i--) {
      if (c > 0 && c % 3 == 0) buf.write(',');
      buf.write(s[i]);
      c++;
    }
    return buf.toString().split('').reversed.join();
  }

  String _fmt(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year} '
          '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return iso;
    }
  }
}

// ─── Status badge row ─────────────────────────────────────────────────────────

class _StatusBadgeRow extends StatelessWidget {
  final CrmLead lead;
  const _StatusBadgeRow({required this.lead});

  static const _pipeline = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won'];

  @override
  Widget build(BuildContext context) {
    final color = _statusColors[lead.status] ?? _kSubtext;
    final isLost = lead.status == 'lost';

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: color.withOpacity(0.2),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(lead.statusLabel,
                style: TextStyle(color: color, fontSize: 13, fontWeight: FontWeight.bold)),
          ),
          if (isLost) ...[
            const SizedBox(width: 8),
            const Text('Cerrado — perdido', style: TextStyle(color: _kSubtext, fontSize: 12)),
          ],
        ]),
        if (!isLost) ...[
          const SizedBox(height: 10),
          _PipelineBar(currentStatus: lead.status, pipeline: _pipeline),
        ],
      ]),
    );
  }
}

class _PipelineBar extends StatelessWidget {
  final String currentStatus;
  final List<String> pipeline;
  const _PipelineBar({required this.currentStatus, required this.pipeline});

  @override
  Widget build(BuildContext context) {
    final currentIdx = pipeline.indexOf(currentStatus);
    return Row(
      children: List.generate(pipeline.length, (i) {
        final done   = i <= currentIdx;
        final color  = _statusColors[pipeline[i]] ?? _kSubtext;
        final isLast = i == pipeline.length - 1;
        return Expanded(
          child: Row(children: [
            Expanded(
              child: Column(children: [
                Container(
                  height: 6,
                  decoration: BoxDecoration(
                    color: done ? color : Colors.white12,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
                const SizedBox(height: 3),
                if (i == currentIdx)
                  Text(
                    _statusLabels[pipeline[i]] ?? pipeline[i],
                    style: TextStyle(color: color, fontSize: 8, fontWeight: FontWeight.bold),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
              ]),
            ),
            if (!isLast) const SizedBox(width: 2),
          ]),
        );
      }),
    );
  }
}

// ─── Section card ─────────────────────────────────────────────────────────────

class _SectionCard extends StatelessWidget {
  final String title;
  final List<Widget> children;
  const _SectionCard({required this.title, required this.children});

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white10),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, style: const TextStyle(color: _kSubtext, fontSize: 11, fontWeight: FontWeight.w600,
              letterSpacing: 0.5)),
          const SizedBox(height: 10),
          ...children,
        ]),
      );
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? sublabel;
  const _InfoRow({required this.icon, required this.label, this.sublabel});

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Row(children: [
          Icon(icon, size: 15, color: _kSubtext),
          const SizedBox(width: 8),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(label, style: const TextStyle(color: _kText, fontSize: 13)),
              if (sublabel != null)
                Text(sublabel!, style: const TextStyle(color: _kSubtext, fontSize: 11)),
            ]),
          ),
        ]),
      );
}

// ─── Activity timeline tile ───────────────────────────────────────────────────

class _ActivityTile extends StatelessWidget {
  final CrmLeadActivity activity;
  const _ActivityTile({required this.activity});

  Color get _color => activity.type == 'status_change'
      ? (_statusColors[activity.newStatus] ?? _kSubtext)
      : _kGold;

  @override
  Widget build(BuildContext context) {
    final icon = _activityIcons[activity.type] ?? Icons.radio_button_unchecked_rounded;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: _color.withOpacity(0.2)),
      ),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Container(
          width: 32, height: 32,
          decoration: BoxDecoration(
            color: _color.withOpacity(0.12),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, size: 15, color: _color),
        ),
        const SizedBox(width: 10),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Text(activity.typeLabel,
                style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.bold)),
            const Spacer(),
            Text(_fmt(activity.activityAt),
                style: const TextStyle(color: _kSubtext, fontSize: 11)),
          ]),
          if (activity.subject != null && activity.subject!.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(activity.subject!, style: const TextStyle(color: _kSubtext, fontSize: 12)),
          ],
          if (activity.type == 'status_change' && activity.newStatus != null) ...[
            const SizedBox(height: 4),
            Row(children: [
              if (activity.oldStatus != null) ...[
                _StatusPill(status: activity.oldStatus!),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 6),
                  child: Icon(Icons.arrow_forward_rounded, size: 12, color: _kSubtext),
                ),
              ],
              _StatusPill(status: activity.newStatus!),
            ]),
          ],
          if (activity.description != null && activity.description!.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(activity.description!,
                style: const TextStyle(color: _kSubtext, fontSize: 12, height: 1.4)),
          ],
          if (activity.agent != null) ...[
            const SizedBox(height: 4),
            Text('por ${activity.agent!.name}',
                style: const TextStyle(color: _kSubtext, fontSize: 11)),
          ],
        ])),
      ]),
    );
  }

  String _fmt(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')} '
          '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return iso;
    }
  }
}

class _StatusPill extends StatelessWidget {
  final String status;
  const _StatusPill({required this.status});

  @override
  Widget build(BuildContext context) {
    final color = _statusColors[status] ?? _kSubtext;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        _statusLabels[status] ?? status,
        style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.bold),
      ),
    );
  }
}

// ─── Action bar ───────────────────────────────────────────────────────────────

class _ActionBar extends StatelessWidget {
  final CrmLead lead;
  final VoidCallback onStatusChanged;
  final VoidCallback onActivityLogged;

  const _ActionBar({
    required this.lead,
    required this.onStatusChanged,
    required this.onActivityLogged,
  });

  @override
  Widget build(BuildContext context) => Container(
        padding: EdgeInsets.fromLTRB(16, 12, 16, 12 + MediaQuery.of(context).padding.bottom),
        decoration: BoxDecoration(
          color: _kSurface,
          border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08))),
        ),
        child: Row(children: [
          Expanded(
            child: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: _kGold,
                side: const BorderSide(color: _kGold),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.swap_horiz_rounded, size: 18),
              label: const Text('Estado', style: TextStyle(fontWeight: FontWeight.bold)),
              onPressed: () => _showChangeStatus(context),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: _kGold,
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.add_comment_rounded, size: 18),
              label: const Text('Actividad', style: TextStyle(fontWeight: FontWeight.bold)),
              onPressed: () => _showLogActivity(context),
            ),
          ),
        ]),
      );

  void _showChangeStatus(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: _kSurface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => _ChangeStatusSheet(lead: lead, onChanged: onStatusChanged),
    );
  }

  void _showLogActivity(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: _kSurface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => _LogActivitySheet(leadId: lead.id, onLogged: onActivityLogged),
    );
  }
}

// ─── Change status sheet ──────────────────────────────────────────────────────

class _ChangeStatusSheet extends StatefulWidget {
  final CrmLead lead;
  final VoidCallback onChanged;
  const _ChangeStatusSheet({required this.lead, required this.onChanged});

  @override
  State<_ChangeStatusSheet> createState() => _ChangeStatusSheetState();
}

class _ChangeStatusSheetState extends State<_ChangeStatusSheet> {
  static const _statuses = [
    ('new',         'Nuevo',       Color(0xFF3498DB)),
    ('contacted',   'Contactado',  Color(0xFF9B59B6)),
    ('qualified',   'Calificado',  Color(0xFF1ABC9C)),
    ('proposal',    'Propuesta',   Color(0xFFE67E22)),
    ('negotiation', 'Negociación', Color(0xFFF39C12)),
    ('won',         'Ganado',      Color(0xFF27AE60)),
    ('lost',        'Perdido',     Color(0xFFE74C3C)),
  ];

  String? _selected;
  final _noteCtrl = TextEditingController();
  bool _saving = false;

  @override
  void dispose() {
    _noteCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Cambiar estado', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8, runSpacing: 8,
            children: _statuses.map((s) {
              if (s.$1 == widget.lead.status) return const SizedBox();
              final sel = _selected == s.$1;
              return GestureDetector(
                onTap: () => setState(() => _selected = s.$1),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: sel ? s.$3.withOpacity(0.2) : _kCard,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: sel ? s.$3 : Colors.white12),
                  ),
                  child: Text(s.$2,
                      style: TextStyle(
                        color: sel ? s.$3 : _kSubtext,
                        fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                        fontSize: 13,
                      )),
                ),
              );
            }).toList(),
          ),
          if (_selected != null) ...[
            const SizedBox(height: 14),
            TextField(
              controller: _noteCtrl,
              style: const TextStyle(color: _kText, fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Nota opcional sobre el cambio...',
                hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
                filled: true,
                fillColor: _kCard,
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                contentPadding: const EdgeInsets.all(12),
              ),
              maxLines: 2,
            ),
          ],
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _kGold,
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: _selected == null || _saving ? null : _save,
              child: _saving
                  ? const SizedBox(height: 18, width: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                  : const Text('Guardar', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    final r = await CrmService().changeStatus(
      widget.lead.id,
      _selected!,
      note: _noteCtrl.text.trim().isEmpty ? null : _noteCtrl.text.trim(),
    );
    if (!mounted) return;
    Navigator.pop(context);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Estado actualizado', backgroundColor: const Color(0xFF2E7D32));
      widget.onChanged();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

// ─── Log activity sheet ───────────────────────────────────────────────────────

class _LogActivitySheet extends StatefulWidget {
  final int leadId;
  final VoidCallback onLogged;
  const _LogActivitySheet({required this.leadId, required this.onLogged});

  @override
  State<_LogActivitySheet> createState() => _LogActivitySheetState();
}

class _LogActivitySheetState extends State<_LogActivitySheet> {
  static const _types = [
    ('call',     'Llamada',   Icons.phone_rounded),
    ('email',    'Email',     Icons.email_rounded),
    ('whatsapp', 'WhatsApp',  Icons.chat_rounded),
    ('visit',    'Visita',    Icons.location_on_rounded),
    ('meeting',  'Reunión',   Icons.groups_rounded),
    ('note',     'Nota',      Icons.note_rounded),
    ('other',    'Otro',      Icons.more_horiz_rounded),
  ];

  String _type = 'call';
  final _subjectCtrl = TextEditingController();
  final _descCtrl    = TextEditingController();
  bool _saving = false;

  @override
  void dispose() {
    _subjectCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Registrar actividad',
              style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 14),
          // Type selector
          SizedBox(
            height: 40,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: _types.map((t) {
                final sel = _type == t.$1;
                return GestureDetector(
                  onTap: () => setState(() => _type = t.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: sel ? _kGold.withOpacity(0.15) : _kCard,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: sel ? _kGold : Colors.white12),
                    ),
                    child: Row(children: [
                      Icon(t.$3, size: 14, color: sel ? _kGold : _kSubtext),
                      const SizedBox(width: 5),
                      Text(t.$2,
                          style: TextStyle(
                            color: sel ? _kGold : _kSubtext,
                            fontSize: 12,
                            fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                          )),
                    ]),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _subjectCtrl,
            style: const TextStyle(color: _kText, fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Asunto *',
              hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
              filled: true,
              fillColor: _kCard,
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
              contentPadding: const EdgeInsets.all(12),
            ),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _descCtrl,
            style: const TextStyle(color: _kText, fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Descripción (opcional)',
              hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
              filled: true,
              fillColor: _kCard,
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
              contentPadding: const EdgeInsets.all(12),
            ),
            maxLines: 3,
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _kGold,
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(height: 18, width: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                  : const Text('Registrar', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _save() async {
    if (_subjectCtrl.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Ingresá un asunto', backgroundColor: Colors.orange[700]);
      return;
    }
    setState(() => _saving = true);
    final r = await CrmService().logActivity(
      leadId:      widget.leadId,
      type:        _type,
      subject:     _subjectCtrl.text.trim(),
      description: _descCtrl.text.trim().isEmpty ? null : _descCtrl.text.trim(),
    );
    if (!mounted) return;
    Navigator.pop(context);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Actividad registrada', backgroundColor: const Color(0xFF2E7D32));
      widget.onLogged();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}
