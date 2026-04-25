import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/event_lead_model.dart';
import 'package:space360_flutter/src/services/agent_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF111111);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

Future<void> showLeadDetailSheet(
  BuildContext context,
  EventLeadModel lead,
  AuthUser user,
) {
  return showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => LeadDetailSheet(lead: lead, user: user),
  );
}

class LeadDetailSheet extends StatefulWidget {
  final EventLeadModel lead;
  final AuthUser user;
  const LeadDetailSheet({super.key, required this.lead, required this.user});

  @override
  State<LeadDetailSheet> createState() => _LeadDetailSheetState();
}

class _LeadDetailSheetState extends State<LeadDetailSheet> {
  final _service   = AgentService();
  Resource<EventLeadModel>? _fullLead;
  bool _showForm   = false;

  // followup form
  String _action   = 'call';
  String _outcome  = 'interested';
  final _notesCtrl = TextEditingController();
  bool _submitting = false;

  static const _actions = [
    ('call',       'Llamada',      Icons.phone_rounded),
    ('whatsapp',   'WhatsApp',     Icons.chat_rounded),
    ('email',      'Email',        Icons.email_rounded),
    ('meeting',    'Reunión',      Icons.people_rounded),
    ('demo',       'Demo',         Icons.directions_car_rounded),
    ('quote_sent', 'Cotización',   Icons.calculate_rounded),
    ('other',      'Otro',         Icons.more_horiz_rounded),
  ];

  static const _outcomes = [
    ('no_answer',       'No contestó',      Color(0xFF757575)),
    ('interested',      'Interesado',       Color(0xFF2E7D32)),
    ('not_interested',  'No interesado',    Color(0xFFC62828)),
    ('thinking',        'Lo está pensando', Color(0xFFF57F17)),
    ('follow_up_later', 'Seguimiento',      Color(0xFF1565C0)),
    ('converted',       'Venta concretada', Color(0xFF1B5E20)),
    ('lost',            'Lead perdido',     Color(0xFF4A0000)),
  ];

  static const _interestColors = {
    'hot': Color(0xFFC62828), 'high': Color(0xFFE65100),
    'medium': Color(0xFFF57F17), 'low': Color(0xFF2E7D32),
  };
  static const _interestLabels = {
    'hot': '🔥 Hot', 'high': 'Alto', 'medium': 'Medio', 'low': 'Bajo',
  };

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _fullLead = null);
    final res = await _service.getLead(widget.lead.id);
    if (mounted) setState(() => _fullLead = res);
  }

  Future<void> _submitFollowup() async {
    setState(() => _submitting = true);
    final res = await _service.addFollowup(
      leadId:  widget.lead.id,
      action:  _action,
      outcome: _outcome,
      notes:   _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
    );
    if (!mounted) return;
    setState(() => _submitting = false);
    if (res is Success<bool>) {
      _notesCtrl.clear();
      setState(() => _showForm = false);
      _load();
      Fluttertoast.showToast(msg: 'Seguimiento registrado', backgroundColor: const Color(0xFF2E7D32));
    } else {
      Fluttertoast.showToast(
        msg: (res as AppError<bool>).message,
        backgroundColor: Colors.red[700],
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    final lead = _fullLead is Success<EventLeadModel>
        ? (_fullLead as Success<EventLeadModel>).data
        : widget.lead;

    return Container(
      margin: const EdgeInsets.only(top: 48),
      decoration: const BoxDecoration(
        color: _kBg,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(0, 0, 0, bottom + 24),
      child: Column(
        children: [
          // drag handle
          const _DragHandle(),
          // header row
          _SheetHeader(lead: widget.lead, interestColors: _interestColors, interestLabels: _interestLabels),
          Expanded(
            child: _fullLead == null
                ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)))
                : _fullLead is AppError
                    ? Center(
                        child: TextButton.icon(
                          onPressed: _load,
                          icon: const Icon(Icons.refresh_rounded, color: _kGold),
                          label: const Text('Reintentar', style: TextStyle(color: _kGold)),
                        ),
                      )
                    : SingleChildScrollView(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // contact info
                            _ContactInfo(lead: lead),
                            if (lead.vehicle != null) ...[
                              const SizedBox(height: 16),
                              _VehicleRow(vehicle: lead.vehicle!),
                            ],
                            if (lead.notes != null && lead.notes!.isNotEmpty) ...[
                              const SizedBox(height: 16),
                              _NotesSection(notes: lead.notes!),
                            ],
                            const SizedBox(height: 20),
                            // followup history
                            _FollowupHeader(
                              count: lead.followups.length,
                              canAdd: widget.user.canAccess('event_dashboard', 'view'),
                              showForm: _showForm,
                              onToggle: () => setState(() => _showForm = !_showForm),
                            ),
                            if (_showForm) ...[
                              const SizedBox(height: 12),
                              _FollowupForm(
                                actions:    _actions,
                                outcomes:   _outcomes,
                                selectedAction:  _action,
                                selectedOutcome: _outcome,
                                notesCtrl:  _notesCtrl,
                                submitting: _submitting,
                                onActionChanged:  (v) => setState(() => _action = v),
                                onOutcomeChanged: (v) => setState(() => _outcome = v),
                                onSubmit: _submitFollowup,
                              ),
                            ],
                            const SizedBox(height: 12),
                            if (lead.followups.isEmpty && !_showForm)
                              const _EmptyFollowups()
                            else
                              ...lead.followups.map((f) => _FollowupCard(followup: f, outcomes: _outcomes)),
                          ],
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}

// ─── Sub-widgets ──────────────────────────────────────────────────────────────

class _DragHandle extends StatelessWidget {
  const _DragHandle();
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 12),
        child: Center(
          child: Container(
            width: 40, height: 4,
            decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2)),
          ),
        ),
      );
}

class _SheetHeader extends StatelessWidget {
  final EventLeadModel lead;
  final Map<String, Color> interestColors;
  final Map<String, String> interestLabels;
  const _SheetHeader({required this.lead, required this.interestColors, required this.interestLabels});

  @override
  Widget build(BuildContext context) {
    final iColor = interestColors[lead.interestLevel] ?? _kSubtext;
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 14),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.white10)),
      ),
      child: Row(children: [
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(lead.name,
                style: const TextStyle(color: _kText, fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 2),
            Text(_formatDate(lead.createdAt),
                style: const TextStyle(color: _kSubtext, fontSize: 12)),
          ]),
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: iColor.withOpacity(0.15),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: iColor.withOpacity(0.4)),
          ),
          child: Text(interestLabels[lead.interestLevel] ?? lead.interestLevel,
              style: TextStyle(color: iColor, fontSize: 12, fontWeight: FontWeight.bold)),
        ),
      ]),
    );
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.day.toString().padLeft(2,'0')}/${dt.month.toString().padLeft(2,'0')}/${dt.year} '
          '${dt.hour.toString().padLeft(2,'0')}:${dt.minute.toString().padLeft(2,'0')}';
    } catch (_) {
      return iso;
    }
  }
}

class _ContactInfo extends StatelessWidget {
  final EventLeadModel lead;
  const _ContactInfo({required this.lead});

  @override
  Widget build(BuildContext context) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: 16),
          _InfoRow(Icons.phone_outlined, lead.phone),
          if (lead.email != null) _InfoRow(Icons.email_outlined, lead.email!),
          if (lead.agent != null) _InfoRow(Icons.person_outline, 'Capturado por ${lead.agent!.name}'),
          if (lead.contacted && lead.contactedBy != null)
            _InfoRow(Icons.check_circle_outline, 'Contactado por ${lead.contactedBy!}',
                color: const Color(0xFF2E7D32)),
        ],
      );
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String text;
  final Color? color;
  const _InfoRow(this.icon, this.text, {this.color});

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(bottom: 6),
        child: Row(children: [
          Icon(icon, size: 15, color: color ?? _kSubtext),
          const SizedBox(width: 8),
          Expanded(child: Text(text,
              style: TextStyle(color: color ?? _kSubtext, fontSize: 13))),
        ]),
      );
}

class _VehicleRow extends StatelessWidget {
  final EventLeadVehicle vehicle;
  const _VehicleRow({required this.vehicle});

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: vehicle.image != null
                ? Image.network(vehicle.image!, width: 52, height: 40, fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _placeholder())
                : _placeholder(),
          ),
          const SizedBox(width: 10),
          Expanded(child: Text(vehicle.name,
              style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.w600))),
          const Icon(Icons.directions_car_outlined, color: _kSubtext, size: 16),
        ]),
      );

  Widget _placeholder() => Container(
        width: 52, height: 40,
        color: const Color(0xFF222222),
        child: const Center(child: Icon(Icons.directions_car_rounded, color: Color(0xFF444444), size: 20)),
      );
}

class _NotesSection extends StatelessWidget {
  final String notes;
  const _NotesSection({required this.notes});

  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: Colors.white10),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Notas', style: TextStyle(color: _kSubtext, fontSize: 11, fontWeight: FontWeight.w600)),
          const SizedBox(height: 4),
          Text(notes, style: const TextStyle(color: _kText, fontSize: 13)),
        ]),
      );
}

class _FollowupHeader extends StatelessWidget {
  final int count;
  final bool canAdd;
  final bool showForm;
  final VoidCallback onToggle;
  const _FollowupHeader({required this.count, required this.canAdd, required this.showForm, required this.onToggle});

  @override
  Widget build(BuildContext context) => Row(children: [
        Text('Seguimiento ($count)',
            style: const TextStyle(color: _kText, fontSize: 15, fontWeight: FontWeight.bold)),
        const Spacer(),
        if (canAdd)
          GestureDetector(
            onTap: onToggle,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: showForm ? Colors.white.withOpacity(0.06) : _kGold.withOpacity(0.12),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: showForm ? Colors.white12 : _kGold.withOpacity(0.4)),
              ),
              child: Row(children: [
                Icon(showForm ? Icons.close_rounded : Icons.add_rounded,
                    size: 14, color: showForm ? _kSubtext : _kGold),
                const SizedBox(width: 4),
                Text(showForm ? 'Cancelar' : 'Agregar',
                    style: TextStyle(
                      color: showForm ? _kSubtext : _kGold, fontSize: 12, fontWeight: FontWeight.bold)),
              ]),
            ),
          ),
      ]);
}

class _EmptyFollowups extends StatelessWidget {
  const _EmptyFollowups();
  @override
  Widget build(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 20),
          child: Column(children: const [
            Icon(Icons.history_rounded, color: _kSubtext, size: 32),
            SizedBox(height: 8),
            Text('Sin seguimientos registrados',
                style: TextStyle(color: _kSubtext, fontSize: 13)),
          ]),
        ),
      );
}

// ─── Add followup form ────────────────────────────────────────────────────────

class _FollowupForm extends StatelessWidget {
  final List<(String, String, IconData)> actions;
  final List<(String, String, Color)> outcomes;
  final String selectedAction;
  final String selectedOutcome;
  final TextEditingController notesCtrl;
  final bool submitting;
  final ValueChanged<String> onActionChanged;
  final ValueChanged<String> onOutcomeChanged;
  final VoidCallback onSubmit;

  const _FollowupForm({
    required this.actions,
    required this.outcomes,
    required this.selectedAction,
    required this.selectedOutcome,
    required this.notesCtrl,
    required this.submitting,
    required this.onActionChanged,
    required this.onOutcomeChanged,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: _kGold.withOpacity(0.25)),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Acción realizada', style: TextStyle(color: _kSubtext, fontSize: 12)),
          const SizedBox(height: 8),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: actions.map((a) {
                final sel = selectedAction == a.$1;
                return GestureDetector(
                  onTap: () => onActionChanged(a.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: sel ? _kGold : Colors.white.withOpacity(0.05),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: sel ? _kGold : Colors.white12),
                    ),
                    child: Row(children: [
                      Icon(a.$3, size: 13, color: sel ? Colors.black : _kSubtext),
                      const SizedBox(width: 4),
                      Text(a.$2, style: TextStyle(
                        color: sel ? Colors.black : _kSubtext,
                        fontSize: 11, fontWeight: sel ? FontWeight.bold : FontWeight.normal)),
                    ]),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 14),
          const Text('Resultado', style: TextStyle(color: _kSubtext, fontSize: 12)),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8, runSpacing: 8,
            children: outcomes.map((o) {
              final sel = selectedOutcome == o.$1;
              return GestureDetector(
                onTap: () => onOutcomeChanged(o.$1),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: sel ? o.$3.withOpacity(0.2) : Colors.white.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: sel ? o.$3 : Colors.white12),
                  ),
                  child: Text(o.$2, style: TextStyle(
                    color: sel ? o.$3 : _kSubtext,
                    fontSize: 11, fontWeight: sel ? FontWeight.bold : FontWeight.normal)),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: notesCtrl,
            maxLines: 2,
            style: const TextStyle(color: _kText, fontSize: 13),
            cursorColor: _kGold,
            decoration: InputDecoration(
              hintText: 'Notas (opcional)',
              hintStyle: const TextStyle(color: _kSubtext, fontSize: 12),
              filled: true,
              fillColor: Colors.white.withOpacity(0.04),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8), borderSide: BorderSide.none),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: _kGold, width: 1.5)),
              contentPadding: const EdgeInsets.all(10),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity, height: 44,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _kGold, foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: submitting ? null : onSubmit,
              child: submitting
                  ? const SizedBox(width: 18, height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(Colors.black)))
                  : const Text('Guardar seguimiento', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            ),
          ),
        ]),
      );
}

// ─── Followup card (timeline entry) ──────────────────────────────────────────

class _FollowupCard extends StatelessWidget {
  final LeadFollowup followup;
  final List<(String, String, Color)> outcomes;
  const _FollowupCard({required this.followup, required this.outcomes});

  static const _actionLabels = {
    'call': 'Llamada', 'whatsapp': 'WhatsApp', 'email': 'Email',
    'meeting': 'Reunión', 'demo': 'Demo', 'quote_sent': 'Cotización', 'other': 'Otro',
  };
  static const _actionIcons = {
    'call': Icons.phone_rounded, 'whatsapp': Icons.chat_rounded,
    'email': Icons.email_rounded, 'meeting': Icons.people_rounded,
    'demo': Icons.directions_car_rounded, 'quote_sent': Icons.calculate_rounded,
    'other': Icons.more_horiz_rounded,
  };

  @override
  Widget build(BuildContext context) {
    final outcomeEntry = outcomes.where((o) => o.$1 == followup.outcome).firstOrNull;
    final outcomeColor = outcomeEntry?.$3 ?? _kSubtext;
    final outcomeLabel = outcomeEntry?.$2 ?? followup.outcome;

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // timeline dot
        Column(children: [
          Container(
            width: 28, height: 28,
            decoration: BoxDecoration(
              color: _kSurface,
              shape: BoxShape.circle,
              border: Border.all(color: Colors.white12),
            ),
            child: Icon(
              _actionIcons[followup.action] ?? Icons.more_horiz_rounded,
              size: 13, color: _kGold,
            ),
          ),
          Container(width: 1, height: 12, color: Colors.white10),
        ]),
        const SizedBox(width: 10),
        Expanded(
          child: Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: _kSurface,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.white10),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Text(_actionLabels[followup.action] ?? followup.action,
                    style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.bold)),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                  decoration: BoxDecoration(
                    color: outcomeColor.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(outcomeLabel,
                      style: TextStyle(color: outcomeColor, fontSize: 10, fontWeight: FontWeight.bold)),
                ),
              ]),
              if (followup.notes != null && followup.notes!.isNotEmpty) ...[
                const SizedBox(height: 6),
                Text(followup.notes!, style: const TextStyle(color: _kSubtext, fontSize: 12)),
              ],
              const SizedBox(height: 6),
              Row(children: [
                if (followup.agent != null) ...[
                  const Icon(Icons.person_outline, size: 11, color: _kSubtext),
                  const SizedBox(width: 3),
                  Text(followup.agent!.name,
                      style: const TextStyle(color: _kSubtext, fontSize: 11)),
                  const SizedBox(width: 10),
                ],
                const Icon(Icons.access_time_rounded, size: 11, color: _kSubtext),
                const SizedBox(width: 3),
                Text(_formatDate(followup.createdAt),
                    style: const TextStyle(color: _kSubtext, fontSize: 11)),
              ]),
            ]),
          ),
        ),
      ],
    );
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
