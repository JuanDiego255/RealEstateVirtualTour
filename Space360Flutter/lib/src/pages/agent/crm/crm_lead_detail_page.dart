import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/appointment_model.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/models/reminder_model.dart';
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

class _CrmLeadDetailPageState extends State<CrmLeadDetailPage>
    with SingleTickerProviderStateMixin {
  late final TabController _tab;
  final _service = CrmService();
  CrmLead? _lead;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 4, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
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
          if (_lead != null)
            IconButton(
              icon: const Icon(Icons.edit_rounded, color: _kSubtext),
              onPressed: () async {
                final updated = await showEditLeadSheet(context, _lead!, user: widget.user);
                if (updated == true) _load();
              },
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _kSubtext),
            onPressed: _load,
          ),
        ],
        bottom: TabBar(
          controller: _tab,
          indicatorColor: _kGold,
          labelColor: _kGold,
          unselectedLabelColor: _kSubtext,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          unselectedLabelStyle: const TextStyle(fontSize: 12),
          tabs: const [
            Tab(text: 'Info'),
            Tab(text: 'Actividad'),
            Tab(text: 'Citas'),
            Tab(text: 'Recordatorios'),
          ],
        ),
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
              : TabBarView(
                  controller: _tab,
                  children: [
                    _InfoTab(lead: _lead!),
                    _ActivityTab(lead: _lead!),
                    _AppointmentsTab(lead: _lead!, user: widget.user),
                    _RemindersTab(lead: _lead!),
                  ],
                ),
      bottomNavigationBar: _lead == null
          ? null
          : _ActionBar(
              lead: _lead!,
              onStatusChanged: _load,
              onActivityLogged: _load,
            ),
    );
  }
}

// ─── Info tab ─────────────────────────────────────────────────────────────────

class _InfoTab extends StatelessWidget {
  final CrmLead lead;
  const _InfoTab({required this.lead});

  @override
  Widget build(BuildContext context) => ListView(
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
            _InfoRow(icon: Icons.phone_rounded,  label: lead.phone,      copyValue: lead.phone),
            if (lead.email != null)
              _InfoRow(icon: Icons.email_rounded, label: lead.email!,    copyValue: lead.email),
            if (lead.whatsapp != null)
              _InfoRow(icon: Icons.chat_rounded,  label: lead.whatsapp!, copyValue: lead.whatsapp),
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
        ],
      );
}

// ─── Activity tab ─────────────────────────────────────────────────────────────

class _ActivityTab extends StatelessWidget {
  final CrmLead lead;
  const _ActivityTab({required this.lead});

  @override
  Widget build(BuildContext context) {
    if (lead.activities.isEmpty) {
      return const Center(
        child: Text('Sin actividades registradas', style: TextStyle(color: _kSubtext, fontSize: 13)),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
      itemCount: lead.activities.length,
      itemBuilder: (_, i) => _ActivityTile(activity: lead.activities[i]),
    );
  }
}

// ─── Appointments tab ─────────────────────────────────────────────────────────

class _AppointmentsTab extends StatefulWidget {
  final CrmLead lead;
  final AuthUser user;
  const _AppointmentsTab({required this.lead, required this.user});

  @override
  State<_AppointmentsTab> createState() => _AppointmentsTabState();
}

class _AppointmentsTabState extends State<_AppointmentsTab> with AutomaticKeepAliveClientMixin {
  final _service = CrmService();
  List<AppointmentModel> _appointments = [];
  bool _loading = true;
  String? _error;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final r = await _service.getLeadAppointments(widget.lead.id);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (r is Success<List<AppointmentModel>>) {
        _appointments = r.data;
      } else if (r is AppError<List<AppointmentModel>>) {
        _error = r.message;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return Scaffold(
      backgroundColor: _kBg,
      floatingActionButton: FloatingActionButton(
        mini: true,
        backgroundColor: _kGold,
        foregroundColor: Colors.black,
        child: const Icon(Icons.add_rounded),
        onPressed: () async {
          final created = await showCreateAppointmentSheet(context, widget.lead);
          if (created == true) _load();
        },
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: _kSubtext)))
              : _appointments.isEmpty
                  ? const Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Icon(Icons.event_busy_rounded, color: _kSubtext, size: 48),
                        SizedBox(height: 10),
                        Text('Sin citas programadas', style: TextStyle(color: _kSubtext)),
                        SizedBox(height: 4),
                        Text('Toca + para agregar una', style: TextStyle(color: _kSubtext, fontSize: 12)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: _kGold,
                      backgroundColor: _kSurface,
                      onRefresh: _load,
                      child: ListView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 80),
                        itemCount: _appointments.length,
                        itemBuilder: (_, i) => _AppointmentCard(
                          appointment: _appointments[i],
                          onStatusUpdate: _load,
                          onEdit: () async {
                            final updated = await showEditAppointmentSheet(context, _appointments[i]);
                            if (updated == true) _load();
                          },
                        ),
                      ),
                    ),
    );
  }
}

// ─── Appointment card (in lead detail) ───────────────────────────────────────

class _AppointmentCard extends StatelessWidget {
  final AppointmentModel appointment;
  final VoidCallback onStatusUpdate;
  final VoidCallback onEdit;
  const _AppointmentCard({required this.appointment, required this.onStatusUpdate, required this.onEdit});

  static const _typeColors = {
    'property_visit': Color(0xFF4CAF50), 'vehicle_visit': Color(0xFF2196F3),
    'meeting': Color(0xFF9C27B0), 'call': Color(0xFFFF9800),
    'video_call': Color(0xFF00BCD4), 'signing': Color(0xFFE91E63), 'other': Color(0xFF607D8B),
  };
  static const _typeIcons = {
    'property_visit': Icons.home_rounded, 'vehicle_visit': Icons.directions_car_rounded,
    'meeting': Icons.groups_rounded, 'call': Icons.phone_rounded,
    'video_call': Icons.videocam_rounded, 'signing': Icons.draw_rounded, 'other': Icons.event_rounded,
  };
  static const _statusColors = {
    'scheduled': Color(0xFF3498DB), 'confirmed': Color(0xFF2ECC71),
    'in_progress': Color(0xFFF39C12), 'completed': Color(0xFF27AE60), 'no_show': Color(0xFF7F8C8D),
  };

  Color get _typeColor  => _typeColors[appointment.type]  ?? const Color(0xFF607D8B);
  IconData get _typeIcon => _typeIcons[appointment.type]  ?? Icons.event_rounded;
  Color get _statusColor => _statusColors[appointment.status] ?? _kSubtext;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(12),
        border: Border(left: BorderSide(color: _typeColor, width: 3)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Icon(_typeIcon, size: 16, color: _typeColor),
          const SizedBox(width: 8),
          Expanded(
            child: Text(appointment.title,
                style: const TextStyle(color: _kText, fontSize: 14, fontWeight: FontWeight.bold)),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
            decoration: BoxDecoration(
              color: _statusColor.withOpacity(0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(appointment.statusLabel,
                style: TextStyle(color: _statusColor, fontSize: 10, fontWeight: FontWeight.bold)),
          ),
        ]),
        const SizedBox(height: 6),
        Row(children: [
          const Icon(Icons.access_time_rounded, size: 12, color: _kSubtext),
          const SizedBox(width: 4),
          Text(_fmtDatetime(appointment.startsAt),
              style: const TextStyle(color: _kSubtext, fontSize: 12)),
        ]),
        if (appointment.location != null && appointment.location!.isNotEmpty) ...[
          const SizedBox(height: 3),
          Row(children: [
            const Icon(Icons.location_on_rounded, size: 12, color: _kSubtext),
            const SizedBox(width: 4),
            Expanded(
              child: Text(appointment.location!,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: _kSubtext, fontSize: 12)),
            ),
          ]),
        ],
        if (_canUpdateStatus) ...[
          const SizedBox(height: 10),
          Wrap(spacing: 6, runSpacing: 6, children: [
            _StatusBtn('Editar', _kGold, () => onEdit()),
            if (appointment.status == 'scheduled')
              _StatusBtn('Confirmar', const Color(0xFF2ECC71), () => _updateStatus(context, 'confirmed')),
            if (appointment.status != 'completed' && appointment.status != 'no_show') ...[
              _StatusBtn('Completar', const Color(0xFF27AE60), () => _showCompleteDialog(context)),
              _StatusBtn('Cancelar', const Color(0xFFE74C3C), () => _updateStatus(context, 'cancelled')),
            ],
          ]),
        ],
      ]),
    );
  }

  bool get _canUpdateStatus =>
      appointment.status != 'completed' && appointment.status != 'cancelled';

  Future<void> _updateStatus(BuildContext context, String status) async {
    final r = await CrmService().updateAppointmentStatus(appointment.id, status);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Estado actualizado', backgroundColor: const Color(0xFF2E7D32));
      onStatusUpdate();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }

  void _showCompleteDialog(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: _kSurface,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _CompleteAppointmentSheet(
        appointmentId: appointment.id,
        onCompleted: onStatusUpdate,
      ),
    );
  }

  String _fmtDatetime(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
      return '${dt.day} ${months[dt.month - 1]} — '
          '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) { return iso; }
  }
}

class _StatusBtn extends StatelessWidget {
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _StatusBtn(this.label, this.color, this.onTap);

  @override
  Widget build(BuildContext context) => GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          decoration: BoxDecoration(
            color: color.withOpacity(0.12),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: color.withOpacity(0.4)),
          ),
          child: Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
        ),
      );
}

class _CompleteAppointmentSheet extends StatefulWidget {
  final int appointmentId;
  final VoidCallback onCompleted;
  const _CompleteAppointmentSheet({required this.appointmentId, required this.onCompleted});

  @override
  State<_CompleteAppointmentSheet> createState() => _CompleteAppointmentSheetState();
}

class _CompleteAppointmentSheetState extends State<_CompleteAppointmentSheet> {
  String? _outcome;
  final _notesCtrl = TextEditingController();
  bool _saving = false;

  static const _outcomes = [
    ('successful',       'Exitosa',              Color(0xFF27AE60)),
    ('follow_up_needed', 'Requiere seguimiento', Color(0xFFF39C12)),
    ('not_interested',   'No interesado',        Color(0xFFE74C3C)),
    ('pending',          'Pendiente',            Color(0xFF3498DB)),
  ];

  @override
  void dispose() { _notesCtrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) => Padding(
        padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Completar cita', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 14),
          const Text('Resultado', style: TextStyle(color: _kSubtext, fontSize: 11)),
          const SizedBox(height: 8),
          Wrap(spacing: 8, runSpacing: 8, children: _outcomes.map((o) {
            final sel = _outcome == o.$1;
            return GestureDetector(
              onTap: () => setState(() => _outcome = o.$1),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                decoration: BoxDecoration(
                  color: sel ? o.$3.withOpacity(0.2) : const Color(0xFF222222),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: sel ? o.$3 : Colors.white12),
                ),
                child: Text(o.$2, style: TextStyle(
                  color: sel ? o.$3 : _kSubtext,
                  fontSize: 12, fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                )),
              ),
            );
          }).toList()),
          const SizedBox(height: 12),
          TextField(
            controller: _notesCtrl,
            style: const TextStyle(color: _kText, fontSize: 13),
            decoration: const InputDecoration(
              hintText: 'Notas (opcional)',
              hintStyle: TextStyle(color: _kSubtext, fontSize: 13),
              filled: true, fillColor: Color(0xFF222222),
              border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10)), borderSide: BorderSide.none),
              contentPadding: EdgeInsets.all(12),
            ),
            maxLines: 2,
          ),
          const SizedBox(height: 14),
          SizedBox(width: double.infinity, child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: _kGold, foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                : const Text('Guardar', style: TextStyle(fontWeight: FontWeight.bold)),
          )),
        ]),
      );

  Future<void> _save() async {
    setState(() => _saving = true);
    final r = await CrmService().updateAppointmentStatus(
      widget.appointmentId, 'completed',
      outcome: _outcome,
      outcomeNotes: _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
    );
    if (!mounted) return;
    Navigator.pop(context);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Cita completada', backgroundColor: const Color(0xFF2E7D32));
      widget.onCompleted();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

// ─── Helper functions (shared across tabs) ───────────────────────────────────

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
    return '$sym${_fmtNum(lead.budgetMin!)} – $sym${_fmtNum(lead.budgetMax!)}';
  }
  if (lead.budgetMin != null) return 'Desde $sym${_fmtNum(lead.budgetMin!)}';
  return 'Hasta $sym${_fmtNum(lead.budgetMax!)}';
}

String _fmtNum(double v) {
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
  final String? copyValue;
  const _InfoRow({required this.icon, required this.label, this.sublabel, this.copyValue});

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
          if (copyValue != null)
            GestureDetector(
              onTap: () {
                Clipboard.setData(ClipboardData(text: copyValue!));
                Fluttertoast.showToast(
                  msg: 'Copiado',
                  backgroundColor: const Color(0xFF2E7D32),
                  toastLength: Toast.LENGTH_SHORT,
                );
              },
              child: const Padding(
                padding: EdgeInsets.only(left: 8),
                child: Icon(Icons.copy_rounded, size: 14, color: _kSubtext),
              ),
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

// ═══════════════════════════════════════════════════════════════════════════════
// Edit lead sheet
// ═══════════════════════════════════════════════════════════════════════════════

Future<bool?> showEditLeadSheet(BuildContext context, CrmLead lead, {AuthUser? user}) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: _kSurface,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (_) => _EditLeadSheet(lead: lead, user: user),
  );
}

class _EditLeadSheet extends StatefulWidget {
  final CrmLead lead;
  final AuthUser? user;
  const _EditLeadSheet({required this.lead, this.user});

  @override
  State<_EditLeadSheet> createState() => _EditLeadSheetState();
}

class _EditLeadSheetState extends State<_EditLeadSheet> {
  late final TextEditingController _nameCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _whatsappCtrl;
  late final TextEditingController _notesCtrl;
  late final TextEditingController _budgetMinCtrl;
  late final TextEditingController _budgetMaxCtrl;
  late final TextEditingController _requirementsCtrl;
  late String _priority;
  late String _currency;
  late String _interestType;
  DateTime? _nextFollowUp;
  bool _saving = false;

  List<CrmAgentModel> _agents = [];
  int? _selectedAgentId;

  static const _priorities = [
    ('low',    'Baja',    Color(0xFF2E7D32)),
    ('medium', 'Media',   Color(0xFFF57F17)),
    ('high',   'Alta',    Color(0xFFE65100)),
    ('urgent', 'Urgente', Color(0xFFC62828)),
  ];

  static const _interestTypes = [
    ('buy',   'Compra'),
    ('rent',  'Arriendo'),
    ('trade', 'Canje'),
  ];

  @override
  void initState() {
    super.initState();
    final l = widget.lead;
    _nameCtrl         = TextEditingController(text: l.name);
    _phoneCtrl        = TextEditingController(text: l.phone);
    _emailCtrl        = TextEditingController(text: l.email ?? '');
    _whatsappCtrl     = TextEditingController(text: l.whatsapp ?? '');
    _notesCtrl        = TextEditingController(text: l.notes ?? '');
    _budgetMinCtrl    = TextEditingController(
        text: l.budgetMin != null ? l.budgetMin!.toStringAsFixed(0) : '');
    _budgetMaxCtrl    = TextEditingController(
        text: l.budgetMax != null ? l.budgetMax!.toStringAsFixed(0) : '');
    _requirementsCtrl = TextEditingController(text: l.requirements ?? '');
    _priority         = l.priority;
    _currency         = l.budgetCurrency ?? 'CRC';
    _interestType     = l.interestType;
    _selectedAgentId  = l.agent?.id;
    if (l.nextFollowUp != null) {
      try { _nextFollowUp = DateTime.parse(l.nextFollowUp!).toLocal(); } catch (_) {}
    }
    if (widget.user != null && (widget.user!.isAdmin)) _loadAgents();
  }

  Future<void> _loadAgents() async {
    final r = await CrmService().getAgents();
    if (!mounted) return;
    if (r is Success<List<CrmAgentModel>>) setState(() => _agents = r.data);
  }

  @override
  void dispose() {
    _nameCtrl.dispose(); _phoneCtrl.dispose(); _emailCtrl.dispose();
    _whatsappCtrl.dispose(); _notesCtrl.dispose();
    _budgetMinCtrl.dispose(); _budgetMaxCtrl.dispose(); _requirementsCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const Expanded(child: Text('Editar lead',
                style: TextStyle(color: _kText, fontSize: 17, fontWeight: FontWeight.bold))),
            IconButton(icon: const Icon(Icons.close_rounded, color: _kSubtext),
                onPressed: () => Navigator.pop(context)),
          ]),
          const SizedBox(height: 14),
          _EditField(controller: _nameCtrl, hint: 'Nombre', icon: Icons.person_rounded),
          const SizedBox(height: 10),
          _EditField(controller: _phoneCtrl, hint: 'Teléfono', icon: Icons.phone_rounded,
              keyboardType: TextInputType.phone),
          const SizedBox(height: 10),
          _EditField(controller: _emailCtrl, hint: 'Email', icon: Icons.email_rounded,
              keyboardType: TextInputType.emailAddress),
          const SizedBox(height: 10),
          _EditField(controller: _whatsappCtrl, hint: 'WhatsApp', icon: Icons.chat_rounded,
              keyboardType: TextInputType.phone),
          const SizedBox(height: 14),
          const Text('Prioridad', style: TextStyle(color: _kSubtext, fontSize: 11)),
          const SizedBox(height: 6),
          Row(children: _priorities.map((p) {
            final sel = _priority == p.$1;
            return Expanded(child: GestureDetector(
              onTap: () => setState(() => _priority = p.$1),
              child: Container(
                margin: const EdgeInsets.only(right: 6),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: sel ? p.$3.withOpacity(0.2) : _kCard,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: sel ? p.$3 : Colors.white12),
                ),
                child: Center(child: Text(p.$2, style: TextStyle(
                  color: sel ? p.$3 : _kSubtext, fontSize: 12,
                  fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                ))),
              ),
            ));
          }).toList()),
          const SizedBox(height: 14),
          // Interest type
          const Text('Tipo de interés', style: TextStyle(color: _kSubtext, fontSize: 11)),
          const SizedBox(height: 6),
          Row(children: _interestTypes.map((t) {
            final sel = _interestType == t.$1;
            return Expanded(child: GestureDetector(
              onTap: () => setState(() => _interestType = t.$1),
              child: Container(
                margin: const EdgeInsets.only(right: 6),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: sel ? _kGold.withOpacity(0.15) : _kCard,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: sel ? _kGold : Colors.white12),
                ),
                child: Center(child: Text(t.$2, style: TextStyle(
                  color: sel ? _kGold : _kSubtext, fontSize: 12,
                  fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                ))),
              ),
            ));
          }).toList()),
          const SizedBox(height: 14),
          // Budget
          const Text('Presupuesto', style: TextStyle(color: _kSubtext, fontSize: 11)),
          const SizedBox(height: 6),
          Row(children: [
            // Currency toggle
            GestureDetector(
              onTap: () => setState(() => _currency = _currency == 'CRC' ? 'USD' : 'CRC'),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: _kGold.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: _kGold.withOpacity(0.4)),
                ),
                child: Text(_currency,
                    style: const TextStyle(color: _kGold, fontSize: 13, fontWeight: FontWeight.bold)),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: TextField(
                controller: _budgetMinCtrl,
                style: const TextStyle(color: _kText, fontSize: 13),
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  hintText: 'Mínimo',
                  hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
                  filled: true, fillColor: _kCard,
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8), borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                ),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: TextField(
                controller: _budgetMaxCtrl,
                style: const TextStyle(color: _kText, fontSize: 13),
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  hintText: 'Máximo',
                  hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
                  filled: true, fillColor: _kCard,
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8), borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                ),
              ),
            ),
          ]),
          const SizedBox(height: 10),
          _EditField(controller: _requirementsCtrl, hint: 'Requerimientos', icon: Icons.checklist_rounded, maxLines: 2),
          const SizedBox(height: 10),
          _EditField(controller: _notesCtrl, hint: 'Notas', icon: Icons.note_rounded, maxLines: 3),
          const SizedBox(height: 10),
          // Agent selector (admins only)
          if (widget.user != null && widget.user!.isAdmin && _agents.isNotEmpty) ...[
            const Text('Agente asignado', style: TextStyle(color: _kSubtext, fontSize: 11)),
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: _kCard,
                borderRadius: BorderRadius.circular(10),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int?>(
                  value: _selectedAgentId,
                  dropdownColor: _kCard,
                  style: const TextStyle(color: _kText, fontSize: 13),
                  icon: const Icon(Icons.keyboard_arrow_down_rounded, color: _kSubtext),
                  isExpanded: true,
                  hint: const Text('Sin asignar', style: TextStyle(color: _kSubtext, fontSize: 13)),
                  items: [
                    const DropdownMenuItem<int?>(value: null, child: Text('Sin asignar', style: TextStyle(color: _kSubtext, fontSize: 13))),
                    ..._agents.map((a) => DropdownMenuItem<int?>(
                      value: a.id,
                      child: Text(a.name, style: const TextStyle(color: _kText, fontSize: 13)),
                    )),
                  ],
                  onChanged: (v) => setState(() => _selectedAgentId = v),
                ),
              ),
            ),
            const SizedBox(height: 10),
          ],
          // Next follow-up date picker
          GestureDetector(
            onTap: _pickFollowUp,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
              decoration: BoxDecoration(color: _kCard, borderRadius: BorderRadius.circular(10)),
              child: Row(children: [
                const Icon(Icons.alarm_rounded, size: 18, color: _kSubtext),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    _nextFollowUp != null ? _fmtFollowUp(_nextFollowUp!) : 'Próximo seguimiento (opcional)',
                    style: TextStyle(
                      color: _nextFollowUp != null ? _kText : _kSubtext,
                      fontSize: 13,
                    ),
                  ),
                ),
                if (_nextFollowUp != null)
                  GestureDetector(
                    onTap: () => setState(() => _nextFollowUp = null),
                    child: const Icon(Icons.clear_rounded, size: 16, color: _kSubtext),
                  ),
              ]),
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(width: double.infinity, child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: _kGold, foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                : const Text('Guardar cambios', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          )),
        ]),
      ),
    );
  }

  Future<void> _pickFollowUp() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _nextFollowUp ?? DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: _nextFollowUp != null
          ? TimeOfDay.fromDateTime(_nextFollowUp!)
          : const TimeOfDay(hour: 9, minute: 0),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (time == null) return;
    setState(() {
      _nextFollowUp = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    });
  }

  String _fmtFollowUp(DateTime dt) {
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}  '
        '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _save() async {
    if (_nameCtrl.text.trim().isEmpty || _phoneCtrl.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Nombre y teléfono son requeridos', backgroundColor: Colors.orange[700]);
      return;
    }
    setState(() => _saving = true);
    final budgetMin = double.tryParse(_budgetMinCtrl.text.trim());
    final budgetMax = double.tryParse(_budgetMaxCtrl.text.trim());
    final r = await CrmService().updateLead(widget.lead.id, {
      'name':            _nameCtrl.text.trim(),
      'phone':           _phoneCtrl.text.trim(),
      if (_emailCtrl.text.trim().isNotEmpty)    'email':         _emailCtrl.text.trim(),
      if (_whatsappCtrl.text.trim().isNotEmpty) 'whatsapp':      _whatsappCtrl.text.trim(),
      'priority':        _priority,
      'interest_type':   _interestType,
      'budget_currency': _currency,
      if (budgetMin != null) 'budget_min': budgetMin,
      if (budgetMax != null) 'budget_max': budgetMax,
      if (_requirementsCtrl.text.trim().isNotEmpty) 'requirements': _requirementsCtrl.text.trim(),
      if (_notesCtrl.text.trim().isNotEmpty)         'notes':        _notesCtrl.text.trim(),
      if (_nextFollowUp != null) 'next_follow_up': _nextFollowUp!.toIso8601String(),
      if (widget.user != null && widget.user!.isAdmin)
        'user_id': _selectedAgentId,
    });
    if (!mounted) return;
    if (r is Success<bool>) {
      Navigator.pop(context, true);
      Fluttertoast.showToast(msg: 'Lead actualizado', backgroundColor: const Color(0xFF2E7D32));
    } else if (r is AppError<bool>) {
      setState(() => _saving = false);
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

class _EditField extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final IconData icon;
  final TextInputType? keyboardType;
  final int maxLines;
  const _EditField({
    required this.controller, required this.hint, required this.icon,
    this.keyboardType, this.maxLines = 1,
  });

  @override
  Widget build(BuildContext context) => TextField(
        controller: controller,
        style: const TextStyle(color: _kText, fontSize: 13),
        keyboardType: keyboardType,
        maxLines: maxLines,
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
          prefixIcon: Icon(icon, size: 18, color: _kSubtext),
          filled: true, fillColor: _kCard,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
        ),
      );
}

// ═══════════════════════════════════════════════════════════════════════════════
// Create appointment sheet
// ═══════════════════════════════════════════════════════════════════════════════

Future<bool?> showCreateAppointmentSheet(BuildContext context, CrmLead lead) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: _kSurface,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (_) => _CreateAppointmentSheet(lead: lead),
  );
}

class _CreateAppointmentSheet extends StatefulWidget {
  final CrmLead lead;
  const _CreateAppointmentSheet({required this.lead});

  @override
  State<_CreateAppointmentSheet> createState() => _CreateAppointmentSheetState();
}

class _CreateAppointmentSheetState extends State<_CreateAppointmentSheet> {
  static const _types = [
    ('vehicle_visit',  'Visita vehículo', Icons.directions_car_rounded),
    ('meeting',        'Reunión',         Icons.groups_rounded),
    ('call',           'Llamada',         Icons.phone_rounded),
    ('video_call',     'Videollamada',    Icons.videocam_rounded),
    ('signing',        'Firma',           Icons.draw_rounded),
    ('other',          'Otro',            Icons.event_rounded),
  ];

  String _type = 'meeting';
  final _titleCtrl    = TextEditingController();
  final _locationCtrl = TextEditingController();
  final _descCtrl     = TextEditingController();
  DateTime _startsAt  = DateTime.now().add(const Duration(hours: 1));
  bool _saving = false;

  @override
  void dispose() {
    _titleCtrl.dispose(); _locationCtrl.dispose(); _descCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const Expanded(child: Text('Nueva cita',
                style: TextStyle(color: _kText, fontSize: 17, fontWeight: FontWeight.bold))),
            IconButton(icon: const Icon(Icons.close_rounded, color: _kSubtext),
                onPressed: () => Navigator.pop(context)),
          ]),
          Text('con ${widget.lead.name}',
              style: const TextStyle(color: _kSubtext, fontSize: 13)),
          const SizedBox(height: 14),
          // Type selector
          SizedBox(height: 40, child: ListView(
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
                    Icon(t.$3, size: 13, color: sel ? _kGold : _kSubtext),
                    const SizedBox(width: 5),
                    Text(t.$2, style: TextStyle(
                      color: sel ? _kGold : _kSubtext, fontSize: 12,
                      fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                    )),
                  ]),
                ),
              );
            }).toList(),
          )),
          const SizedBox(height: 12),
          _EditField(controller: _titleCtrl, hint: 'Título *', icon: Icons.title_rounded),
          const SizedBox(height: 10),
          // Date & time picker
          GestureDetector(
            onTap: _pickDateTime,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
              decoration: BoxDecoration(
                color: _kCard,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(children: [
                const Icon(Icons.access_time_rounded, size: 18, color: _kSubtext),
                const SizedBox(width: 10),
                Text(_fmtDatetimeLocal(_startsAt),
                    style: const TextStyle(color: _kText, fontSize: 13)),
              ]),
            ),
          ),
          const SizedBox(height: 10),
          _EditField(controller: _locationCtrl, hint: 'Ubicación (opcional)', icon: Icons.location_on_rounded),
          const SizedBox(height: 10),
          _EditField(controller: _descCtrl, hint: 'Descripción (opcional)', icon: Icons.notes_rounded, maxLines: 2),
          const SizedBox(height: 16),
          SizedBox(width: double.infinity, child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: _kGold, foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                : const Text('Programar cita', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          )),
        ]),
      ),
    );
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _startsAt,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_startsAt),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (time == null) return;
    setState(() {
      _startsAt = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    });
  }

  String _fmtDatetimeLocal(DateTime dt) {
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}  '
        '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _save() async {
    if (_titleCtrl.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Ingresá un título', backgroundColor: Colors.orange[700]);
      return;
    }
    setState(() => _saving = true);
    final r = await CrmService().createAppointment(
      leadId:      widget.lead.id,
      title:       _titleCtrl.text.trim(),
      type:        _type,
      startsAt:    _startsAt.toIso8601String(),
      location:    _locationCtrl.text.trim().isEmpty ? null : _locationCtrl.text.trim(),
      description: _descCtrl.text.trim().isEmpty ? null : _descCtrl.text.trim(),
    );
    if (!mounted) return;
    if (r is Success<int>) {
      Navigator.pop(context, true);
      Fluttertoast.showToast(msg: 'Cita programada', backgroundColor: const Color(0xFF2E7D32));
    } else if (r is AppError<int>) {
      setState(() => _saving = false);
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Edit appointment sheet
// ═══════════════════════════════════════════════════════════════════════════════

Future<bool?> showEditAppointmentSheet(BuildContext context, AppointmentModel appointment) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: _kSurface,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (_) => _EditAppointmentSheet(appointment: appointment),
  );
}

class _EditAppointmentSheet extends StatefulWidget {
  final AppointmentModel appointment;
  const _EditAppointmentSheet({required this.appointment});

  @override
  State<_EditAppointmentSheet> createState() => _EditAppointmentSheetState();
}

class _EditAppointmentSheetState extends State<_EditAppointmentSheet> {
  static const _types = [
    ('vehicle_visit',  'Visita vehículo', Icons.directions_car_rounded),
    ('meeting',        'Reunión',         Icons.groups_rounded),
    ('call',           'Llamada',         Icons.phone_rounded),
    ('video_call',     'Videollamada',    Icons.videocam_rounded),
    ('signing',        'Firma',           Icons.draw_rounded),
    ('other',          'Otro',            Icons.event_rounded),
  ];

  late String _type;
  late final TextEditingController _titleCtrl;
  late final TextEditingController _locationCtrl;
  late final TextEditingController _descCtrl;
  late DateTime _startsAt;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final a = widget.appointment;
    _type        = a.type;
    _titleCtrl    = TextEditingController(text: a.title);
    _locationCtrl = TextEditingController(text: a.location ?? '');
    _descCtrl     = TextEditingController(text: a.description ?? '');
    try {
      _startsAt = DateTime.parse(a.startsAt).toLocal();
    } catch (_) {
      _startsAt = DateTime.now().add(const Duration(hours: 1));
    }
  }

  @override
  void dispose() {
    _titleCtrl.dispose(); _locationCtrl.dispose(); _descCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const Expanded(child: Text('Editar cita',
                style: TextStyle(color: _kText, fontSize: 17, fontWeight: FontWeight.bold))),
            IconButton(icon: const Icon(Icons.close_rounded, color: _kSubtext),
                onPressed: () => Navigator.pop(context)),
          ]),
          const SizedBox(height: 14),
          SizedBox(height: 40, child: ListView(
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
                    Icon(t.$3, size: 13, color: sel ? _kGold : _kSubtext),
                    const SizedBox(width: 5),
                    Text(t.$2, style: TextStyle(
                      color: sel ? _kGold : _kSubtext, fontSize: 12,
                      fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                    )),
                  ]),
                ),
              );
            }).toList(),
          )),
          const SizedBox(height: 12),
          _EditField(controller: _titleCtrl, hint: 'Título *', icon: Icons.title_rounded),
          const SizedBox(height: 10),
          GestureDetector(
            onTap: _pickDateTime,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
              decoration: BoxDecoration(color: _kCard, borderRadius: BorderRadius.circular(10)),
              child: Row(children: [
                const Icon(Icons.access_time_rounded, size: 18, color: _kSubtext),
                const SizedBox(width: 10),
                Text(_fmtDatetimeLocal(_startsAt),
                    style: const TextStyle(color: _kText, fontSize: 13)),
              ]),
            ),
          ),
          const SizedBox(height: 10),
          _EditField(controller: _locationCtrl, hint: 'Ubicación (opcional)', icon: Icons.location_on_rounded),
          const SizedBox(height: 10),
          _EditField(controller: _descCtrl, hint: 'Descripción (opcional)', icon: Icons.notes_rounded, maxLines: 2),
          const SizedBox(height: 16),
          SizedBox(width: double.infinity, child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: _kGold, foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                : const Text('Guardar cambios', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          )),
        ]),
      ),
    );
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _startsAt,
      firstDate: DateTime.now().subtract(const Duration(days: 1)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_startsAt),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (time == null) return;
    setState(() {
      _startsAt = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    });
  }

  String _fmtDatetimeLocal(DateTime dt) {
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}  '
        '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _save() async {
    if (_titleCtrl.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Ingresá un título', backgroundColor: Colors.orange[700]);
      return;
    }
    setState(() => _saving = true);
    final r = await CrmService().updateAppointment(widget.appointment.id, {
      'title':       _titleCtrl.text.trim(),
      'type':        _type,
      'starts_at':   _startsAt.toIso8601String(),
      'location':    _locationCtrl.text.trim().isEmpty ? null : _locationCtrl.text.trim(),
      'description': _descCtrl.text.trim().isEmpty ? null : _descCtrl.text.trim(),
    });
    if (!mounted) return;
    if (r is Success<bool>) {
      Navigator.pop(context, true);
      Fluttertoast.showToast(msg: 'Cita actualizada', backgroundColor: const Color(0xFF2E7D32));
    } else if (r is AppError<bool>) {
      setState(() => _saving = false);
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Reminders tab
// ═══════════════════════════════════════════════════════════════════════════════

class _RemindersTab extends StatefulWidget {
  final CrmLead lead;
  const _RemindersTab({required this.lead});

  @override
  State<_RemindersTab> createState() => _RemindersTabState();
}

class _RemindersTabState extends State<_RemindersTab> with AutomaticKeepAliveClientMixin {
  final _service = CrmService();
  List<ReminderModel> _reminders = [];
  bool _loading = true;
  String? _error;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    final r = await _service.getLeadReminders(widget.lead.id);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (r is Success<List<ReminderModel>>) {
        _reminders = r.data;
      } else if (r is AppError<List<ReminderModel>>) {
        _error = r.message;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return Scaffold(
      backgroundColor: _kBg,
      floatingActionButton: FloatingActionButton(
        mini: true,
        backgroundColor: _kGold,
        foregroundColor: Colors.black,
        child: const Icon(Icons.add_rounded),
        onPressed: () async {
          final created = await showCreateReminderSheet(context, widget.lead);
          if (created == true) _load();
        },
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: _kSubtext)))
              : _reminders.isEmpty
                  ? const Center(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Icon(Icons.alarm_off_rounded, color: _kSubtext, size: 48),
                        SizedBox(height: 10),
                        Text('Sin recordatorios', style: TextStyle(color: _kSubtext)),
                        SizedBox(height: 4),
                        Text('Toca + para agregar uno', style: TextStyle(color: _kSubtext, fontSize: 12)),
                      ]),
                    )
                  : RefreshIndicator(
                      color: _kGold,
                      backgroundColor: _kSurface,
                      onRefresh: _load,
                      child: ListView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 80),
                        itemCount: _reminders.length,
                        itemBuilder: (_, i) => _ReminderCard(
                          reminder: _reminders[i],
                          onAction: _load,
                        ),
                      ),
                    ),
    );
  }
}

class _ReminderCard extends StatelessWidget {
  final ReminderModel reminder;
  final VoidCallback onAction;
  const _ReminderCard({required this.reminder, required this.onAction});

  static const _priorityColors = {
    'urgent': Color(0xFFC62828), 'high': Color(0xFFE65100),
    'medium': Color(0xFFF57F17), 'low':  Color(0xFF2E7D32),
  };

  Color get _color => _priorityColors[reminder.priority] ?? _kSubtext;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: reminder.isOverdue ? const Color(0xFFE74C3C).withOpacity(0.5) : _color.withOpacity(0.25),
        ),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Icon(Icons.alarm_rounded, size: 16, color: _color),
          const SizedBox(width: 8),
          Expanded(
            child: Text(reminder.title,
                style: const TextStyle(color: _kText, fontSize: 14, fontWeight: FontWeight.bold)),
          ),
          if (reminder.isOverdue)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFE74C3C).withOpacity(0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Text('Vencido',
                  style: TextStyle(color: Color(0xFFE74C3C), fontSize: 10, fontWeight: FontWeight.bold)),
            ),
        ]),
        if (reminder.description != null && reminder.description!.isNotEmpty) ...[
          const SizedBox(height: 4),
          Text(reminder.description!, style: const TextStyle(color: _kSubtext, fontSize: 12, height: 1.4)),
        ],
        const SizedBox(height: 8),
        Row(children: [
          const Icon(Icons.access_time_rounded, size: 12, color: _kSubtext),
          const SizedBox(width: 4),
          Text(_fmtRemindAt(reminder.remindAt),
              style: TextStyle(
                color: reminder.isOverdue ? const Color(0xFFE74C3C) : _kSubtext,
                fontSize: 12,
              )),
          const Spacer(),
          _ActionBtn(Icons.snooze_rounded, 'Posponer', _kSubtext,
              () => _snooze(context)),
          const SizedBox(width: 8),
          _ActionBtn(Icons.check_rounded, 'Listo', const Color(0xFF27AE60),
              () => _complete(context)),
          const SizedBox(width: 8),
          _ActionBtn(Icons.delete_outline_rounded, 'Borrar', const Color(0xFFE74C3C),
              () => _delete(context)),
        ]),
      ]),
    );
  }

  Future<void> _complete(BuildContext context) async {
    final r = await CrmService().completeReminder(reminder.id);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Recordatorio completado', backgroundColor: const Color(0xFF2E7D32));
      onAction();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }

  Future<void> _snooze(BuildContext context) async {
    // Show snooze options
    final minutes = await showModalBottomSheet<int>(
      context: context,
      backgroundColor: _kSurface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Padding(
            padding: EdgeInsets.all(16),
            child: Text('Posponer por...', style: TextStyle(color: _kText, fontSize: 15, fontWeight: FontWeight.bold)),
          ),
          ...[
            (15, '15 minutos'), (30, '30 minutos'), (60, '1 hora'),
            (180, '3 horas'), (1440, '1 día'),
          ].map((o) => ListTile(
            leading: const Icon(Icons.snooze_rounded, color: _kSubtext, size: 18),
            title: Text(o.$2, style: const TextStyle(color: _kText, fontSize: 14)),
            onTap: () => Navigator.pop(context, o.$1),
          )),
          const SizedBox(height: 8),
        ],
      ),
    );
    if (minutes == null) return;
    final r = await CrmService().snoozeReminder(reminder.id, minutes: minutes);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Pospuesto', backgroundColor: const Color(0xFF2E7D32));
      onAction();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }

  Future<void> _delete(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: _kSurface,
        title: const Text('Borrar recordatorio', style: TextStyle(color: _kText, fontSize: 16)),
        content: Text('¿Eliminar "${reminder.title}"?',
            style: const TextStyle(color: _kSubtext, fontSize: 13)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar', style: TextStyle(color: _kSubtext)),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Borrar', style: TextStyle(color: Color(0xFFE74C3C))),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    final r = await CrmService().deleteReminder(reminder.id);
    if (r is Success<bool>) {
      Fluttertoast.showToast(msg: 'Recordatorio eliminado', backgroundColor: const Color(0xFF2E7D32));
      onAction();
    } else if (r is AppError<bool>) {
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }

  String _fmtRemindAt(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
      return '${dt.day} ${months[dt.month - 1]}  '
          '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) { return iso; }
  }
}

class _ActionBtn extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _ActionBtn(this.icon, this.label, this.color, this.onTap);

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
            Icon(icon, size: 13, color: color),
            const SizedBox(width: 4),
            Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
          ]),
        ),
      );
}

// ─── Create reminder sheet ────────────────────────────────────────────────────

Future<bool?> showCreateReminderSheet(BuildContext context, CrmLead lead) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: _kSurface,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (_) => _CreateReminderSheet(lead: lead),
  );
}

class _CreateReminderSheet extends StatefulWidget {
  final CrmLead lead;
  const _CreateReminderSheet({required this.lead});

  @override
  State<_CreateReminderSheet> createState() => _CreateReminderSheetState();
}

class _CreateReminderSheetState extends State<_CreateReminderSheet> {
  final _titleCtrl = TextEditingController();
  final _descCtrl  = TextEditingController();
  String _priority  = 'medium';
  DateTime _remindAt = DateTime.now().add(const Duration(hours: 1));
  bool _saving = false;

  static const _priorities = [
    ('low',    'Baja',    Color(0xFF2E7D32)),
    ('medium', 'Media',   Color(0xFFF57F17)),
    ('high',   'Alta',    Color(0xFFE65100)),
    ('urgent', 'Urgente', Color(0xFFC62828)),
  ];

  @override
  void dispose() {
    _titleCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const Expanded(child: Text('Nuevo recordatorio',
                style: TextStyle(color: _kText, fontSize: 17, fontWeight: FontWeight.bold))),
            IconButton(icon: const Icon(Icons.close_rounded, color: _kSubtext),
                onPressed: () => Navigator.pop(context)),
          ]),
          Text('para ${widget.lead.name}', style: const TextStyle(color: _kSubtext, fontSize: 13)),
          const SizedBox(height: 14),
          TextField(
            controller: _titleCtrl,
            style: const TextStyle(color: _kText, fontSize: 13),
            decoration: const InputDecoration(
              hintText: 'Título *',
              hintStyle: TextStyle(color: _kSubtext, fontSize: 13),
              prefixIcon: Icon(Icons.alarm_rounded, size: 18, color: _kSubtext),
              filled: true, fillColor: _kCard,
              border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10)), borderSide: BorderSide.none),
              contentPadding: EdgeInsets.symmetric(vertical: 12, horizontal: 12),
            ),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _descCtrl,
            style: const TextStyle(color: _kText, fontSize: 13),
            maxLines: 2,
            decoration: const InputDecoration(
              hintText: 'Descripción (opcional)',
              hintStyle: TextStyle(color: _kSubtext, fontSize: 13),
              filled: true, fillColor: _kCard,
              border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10)), borderSide: BorderSide.none),
              contentPadding: EdgeInsets.all(12),
            ),
          ),
          const SizedBox(height: 14),
          const Text('Prioridad', style: TextStyle(color: _kSubtext, fontSize: 11)),
          const SizedBox(height: 6),
          Row(children: _priorities.map((p) {
            final sel = _priority == p.$1;
            return Expanded(child: GestureDetector(
              onTap: () => setState(() => _priority = p.$1),
              child: Container(
                margin: const EdgeInsets.only(right: 6),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: sel ? p.$3.withOpacity(0.2) : _kCard,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: sel ? p.$3 : Colors.white12),
                ),
                child: Center(child: Text(p.$2, style: TextStyle(
                  color: sel ? p.$3 : _kSubtext, fontSize: 12,
                  fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                ))),
              ),
            ));
          }).toList()),
          const SizedBox(height: 12),
          GestureDetector(
            onTap: _pickDateTime,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
              decoration: BoxDecoration(color: _kCard, borderRadius: BorderRadius.circular(10)),
              child: Row(children: [
                const Icon(Icons.access_time_rounded, size: 18, color: _kSubtext),
                const SizedBox(width: 10),
                Text(_fmtDt(_remindAt), style: const TextStyle(color: _kText, fontSize: 13)),
              ]),
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(width: double.infinity, child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: _kGold, foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                : const Text('Crear recordatorio', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          )),
        ]),
      ),
    );
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _remindAt,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_remindAt),
      builder: (ctx, child) => Theme(
        data: ThemeData.dark().copyWith(
          colorScheme: const ColorScheme.dark(primary: _kGold, surface: _kSurface),
        ),
        child: child!,
      ),
    );
    if (time == null) return;
    setState(() {
      _remindAt = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    });
  }

  String _fmtDt(DateTime dt) {
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}  '
        '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _save() async {
    if (_titleCtrl.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Ingresá un título', backgroundColor: Colors.orange[700]);
      return;
    }
    setState(() => _saving = true);
    final r = await CrmService().createReminder(
      leadId:      widget.lead.id,
      title:       _titleCtrl.text.trim(),
      remindAt:    _remindAt.toIso8601String(),
      priority:    _priority,
      description: _descCtrl.text.trim().isEmpty ? null : _descCtrl.text.trim(),
    );
    if (!mounted) return;
    if (r is Success<int>) {
      Navigator.pop(context, true);
      Fluttertoast.showToast(msg: 'Recordatorio creado', backgroundColor: const Color(0xFF2E7D32));
    } else if (r is AppError<int>) {
      setState(() => _saving = false);
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

// ─── Missing import for ReminderModel ─────────────────────────────────────────
// (resolved via top-level import in file header)
