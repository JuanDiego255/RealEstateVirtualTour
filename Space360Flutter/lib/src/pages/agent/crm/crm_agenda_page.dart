import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/appointment_model.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_lead_detail_page.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/crm_refresh_bus.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

const _typeColors = {
  'property_visit': Color(0xFF4CAF50),
  'vehicle_visit':  Color(0xFF2196F3),
  'meeting':        Color(0xFF9C27B0),
  'call':           Color(0xFFFF9800),
  'video_call':     Color(0xFF00BCD4),
  'signing':        Color(0xFFE91E63),
  'other':          Color(0xFF607D8B),
};

const _typeIcons = {
  'property_visit': Icons.home_rounded,
  'vehicle_visit':  Icons.directions_car_rounded,
  'meeting':        Icons.groups_rounded,
  'call':           Icons.phone_rounded,
  'video_call':     Icons.videocam_rounded,
  'signing':        Icons.draw_rounded,
  'other':          Icons.event_rounded,
};

const _statusColors = {
  'scheduled':   Color(0xFF3498DB),
  'confirmed':   Color(0xFF2ECC71),
  'in_progress': Color(0xFFF39C12),
  'completed':   Color(0xFF27AE60),
  'no_show':     Color(0xFF7F8C8D),
};

class CrmAgendaPage extends StatefulWidget {
  final AuthUser user;
  const CrmAgendaPage({super.key, required this.user});

  @override
  State<CrmAgendaPage> createState() => _CrmAgendaPageState();
}

class _CrmAgendaPageState extends State<CrmAgendaPage> {
  final _service = CrmService();
  List<AppointmentModel> _appointments = [];
  bool _loading = true;
  String? _error;
  int _days = 14;

  @override
  void initState() {
    super.initState();
    _load();
    CrmRefreshBus.instance.addListener(_onBusSignal);
  }

  @override
  void dispose() {
    CrmRefreshBus.instance.removeListener(_onBusSignal);
    super.dispose();
  }

  void _onBusSignal() {
    if (mounted) _load(silent: true);
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() { _loading = true; _error = null; });
    final r = await _service.getAgenda(days: _days);
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
    if (_loading) {
      return const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)));
    }
    if (_error != null) {
      return ErrorState(message: _error!, onRetry: () => _load());
    }
    return Column(children: [
      // Days filter chips
      _DaysFilter(
        selected: _days,
        onChanged: (d) { setState(() => _days = d); _load(); },
      ),
      Expanded(
        child: _appointments.isEmpty
            ? const EmptyState(
                message: 'Sin citas programadas',
                icon: Icons.event_busy_rounded,
              )
            : RefreshIndicator(
                color: _kGold,
                backgroundColor: _kSurface,
                onRefresh: () => _load(),
                child: ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  itemCount: _appointments.length,
                  itemBuilder: (context, i) {
                    final a = _appointments[i];
                    final prev = i > 0 ? _appointments[i - 1] : null;
                    final showDayHeader = prev == null || !_sameDay(prev.startsAt, a.startsAt);
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (showDayHeader) _DayHeader(iso: a.startsAt),
                        _AppointmentCard(
                          appointment: a,
                          onTap: a.lead != null
                              ? () async {
                                  await Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => CrmLeadDetailPage(
                                        leadId: a.lead!.id,
                                        user: widget.user,
                                      ),
                                    ),
                                  );
                                  _load();
                                }
                              : null,
                        ),
                      ],
                    );
                  },
                ),
              ),
      ),
    ]);
  }

  bool _sameDay(String a, String b) {
    try {
      final da = DateTime.parse(a).toLocal();
      final db = DateTime.parse(b).toLocal();
      return da.year == db.year && da.month == db.month && da.day == db.day;
    } catch (_) {
      return false;
    }
  }
}

// ─── Days filter ──────────────────────────────────────────────────────────────

class _DaysFilter extends StatelessWidget {
  final int selected;
  final ValueChanged<int> onChanged;
  const _DaysFilter({required this.selected, required this.onChanged});

  static const _options = [(7, 'Próx. 7d'), (14, 'Próx. 14d'), (30, 'Próx. 30d')];

  @override
  Widget build(BuildContext context) => SizedBox(
        height: 40,
        child: ListView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          scrollDirection: Axis.horizontal,
          children: _options.map((o) {
            final sel = selected == o.$1;
            return GestureDetector(
              onTap: () => onChanged(o.$1),
              child: Container(
                margin: const EdgeInsets.only(right: 8),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                decoration: BoxDecoration(
                  color: sel ? _kGold.withOpacity(0.15) : _kSurface,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: sel ? _kGold : Colors.white12),
                ),
                child: Text(o.$2,
                    style: TextStyle(
                      color: sel ? _kGold : _kSubtext,
                      fontSize: 12,
                      fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                    )),
              ),
            );
          }).toList(),
        ),
      );
}

// ─── Day header ───────────────────────────────────────────────────────────────

class _DayHeader extends StatelessWidget {
  final String iso;
  const _DayHeader({required this.iso});

  @override
  Widget build(BuildContext context) {
    final dt  = DateTime.tryParse(iso)?.toLocal() ?? DateTime.now();
    final now = DateTime.now();
    final isToday    = dt.year == now.year && dt.month == now.month && dt.day == now.day;
    final isTomorrow = dt.year == now.year && dt.month == now.month && dt.day == now.day + 1;
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    const days   = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

    final label = isToday
        ? 'Hoy'
        : isTomorrow
            ? 'Mañana'
            : '${days[dt.weekday % 7]} ${dt.day} ${months[dt.month - 1]}';

    return Padding(
      padding: const EdgeInsets.only(top: 16, bottom: 8),
      child: Row(children: [
        Text(label,
            style: TextStyle(
              color: isToday ? _kGold : _kText,
              fontSize: 13,
              fontWeight: FontWeight.bold,
            )),
        const SizedBox(width: 8),
        Expanded(child: Divider(color: Colors.white.withOpacity(0.08))),
      ]),
    );
  }
}

// ─── Appointment card ─────────────────────────────────────────────────────────

class _AppointmentCard extends StatelessWidget {
  final AppointmentModel appointment;
  final VoidCallback? onTap;
  const _AppointmentCard({required this.appointment, this.onTap});

  Color get _typeColor  => _typeColors[appointment.type]  ?? const Color(0xFF607D8B);
  IconData get _typeIcon => _typeIcons[appointment.type]  ?? Icons.event_rounded;
  Color get _statusColor => _statusColors[appointment.status] ?? _kSubtext;

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
          border: Border(left: BorderSide(color: _typeColor, width: 3)),
        ),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
              color: _typeColor.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: Icon(_typeIcon, size: 18, color: _typeColor),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
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
            const SizedBox(height: 3),
            Row(children: [
              const Icon(Icons.access_time_rounded, size: 12, color: _kSubtext),
              const SizedBox(width: 4),
              Text(_formatTime(appointment.startsAt),
                  style: const TextStyle(color: _kSubtext, fontSize: 12)),
              if (appointment.endsAt != null) ...[
                Text(' – ${_formatTime(appointment.endsAt!)}',
                    style: const TextStyle(color: _kSubtext, fontSize: 12)),
              ],
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
            if (appointment.lead != null) ...[
              const SizedBox(height: 3),
              Row(children: [
                const Icon(Icons.person_outline_rounded, size: 12, color: _kSubtext),
                const SizedBox(width: 4),
                Text(appointment.lead!.name,
                    style: const TextStyle(color: _kSubtext, fontSize: 12)),
              ]),
            ],
            if (appointment.isToday && appointment.status == 'scheduled') ...[
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _kGold.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: _kGold.withOpacity(0.3)),
                ),
                child: const Text('Hoy', style: TextStyle(color: _kGold, fontSize: 10, fontWeight: FontWeight.bold)),
              ),
            ],
          ])),
        ]),
      ),
    );
  }

  String _formatTime(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return '';
    }
  }
}
