import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/crm_refresh_bus.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

// Pipeline status colors mirrored from other CRM pages
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

const _activityLabels = {
  'call':          'Llamada',
  'email':         'Email',
  'whatsapp':      'WhatsApp',
  'visit':         'Visita',
  'meeting':       'Reunión',
  'note':          'Nota',
  'status_change': 'Cambio estado',
  'other':         'Otro',
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

class CrmAnalyticsPage extends StatefulWidget {
  final AuthUser user;
  const CrmAnalyticsPage({super.key, required this.user});

  @override
  State<CrmAnalyticsPage> createState() => _CrmAnalyticsPageState();
}

class _CrmAnalyticsPageState extends State<CrmAnalyticsPage>
    with AutomaticKeepAliveClientMixin {
  final _service = CrmService();
  CrmAnalyticsModel? _data;
  bool _loading = true;
  String? _error;

  late DateTime _selectedMonth;

  static const _monthNames = [
    '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre',
  ];

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _selectedMonth = DateTime(now.year, now.month, 1);
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
    final r = await _service.getAnalytics(
      month: _selectedMonth.month,
      year:  _selectedMonth.year,
    );
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (r is Success<CrmAnalyticsModel>) {
        _data = r.data;
      } else if (r is AppError<CrmAnalyticsModel>) {
        _error = r.message;
      }
    });
  }

  void _prevMonth() {
    setState(() {
      _selectedMonth = DateTime(_selectedMonth.year, _selectedMonth.month - 1, 1);
    });
    _load();
  }

  void _nextMonth() {
    final now   = DateTime.now();
    final next  = DateTime(_selectedMonth.year, _selectedMonth.month + 1, 1);
    if (next.isAfter(DateTime(now.year, now.month, 1))) return;
    setState(() => _selectedMonth = next);
    _load();
  }

  bool get _isCurrentMonth {
    final now = DateTime.now();
    return _selectedMonth.month == now.month && _selectedMonth.year == now.year;
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)),
      );
    }
    if (_error != null) {
      return Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Text(_error!, style: const TextStyle(color: _kSubtext)),
          const SizedBox(height: 12),
          TextButton(
            onPressed: () => _load(),
            child: const Text('Reintentar', style: TextStyle(color: _kGold)),
          ),
        ]),
      );
    }
    final d = _data!;
    return RefreshIndicator(
      color: _kGold,
      backgroundColor: _kSurface,
      onRefresh: () => _load(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
        children: [
          // ── Month navigator ──────────────────────────────────
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
            decoration: BoxDecoration(
              color: _kSurface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.white10),
            ),
            child: Row(children: [
              IconButton(
                icon: const Icon(Icons.chevron_left_rounded, color: _kSubtext),
                onPressed: _prevMonth,
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
              ),
              Expanded(
                child: Center(
                  child: Text(
                    '${_monthNames[_selectedMonth.month]} ${_selectedMonth.year}',
                    style: TextStyle(
                      color: _isCurrentMonth ? _kGold : _kText,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
              IconButton(
                icon: Icon(Icons.chevron_right_rounded,
                    color: _isCurrentMonth ? _kSubtext.withOpacity(0.3) : _kSubtext),
                onPressed: _isCurrentMonth ? null : _nextMonth,
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
              ),
            ]),
          ),
          _MonthStatsSection(data: d, isCurrentMonth: _isCurrentMonth),
          const SizedBox(height: 16),
          _PipelineFunnelSection(data: d),
          if (d.activitiesToday.isNotEmpty) ...[
            const SizedBox(height: 16),
            _ActivitiesTodaySection(data: d, isCurrentMonth: _isCurrentMonth),
          ],
          if (d.topAgents.isNotEmpty) ...[
            const SizedBox(height: 16),
            _TopAgentsSection(data: d),
          ],
        ],
      ),
    );
  }
}

// ─── Month stats ──────────────────────────────────────────────────────────────

class _MonthStatsSection extends StatelessWidget {
  final CrmAnalyticsModel data;
  final bool isCurrentMonth;
  const _MonthStatsSection({required this.data, this.isCurrentMonth = true});

  @override
  Widget build(BuildContext context) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Text('ESTE MES', style: TextStyle(color: _kSubtext, fontSize: 10, letterSpacing: 1.2, fontWeight: FontWeight.w600)),
      const SizedBox(height: 10),
      // Conversion rate hero card
      Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [_kGold.withOpacity(0.15), _kGold.withOpacity(0.05)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: _kGold.withOpacity(0.3)),
        ),
        child: Row(children: [
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('${data.conversionRate.toStringAsFixed(1)}%',
                style: const TextStyle(
                  color: _kGold,
                  fontSize: 36,
                  fontWeight: FontWeight.bold,
                  height: 1,
                )),
            const SizedBox(height: 4),
            const Text('Tasa de conversión', style: TextStyle(color: _kSubtext, fontSize: 12)),
          ]),
          const Spacer(),
          const Icon(Icons.trending_up_rounded, color: _kGold, size: 48),
        ]),
      ),
      const SizedBox(height: 10),
      // 3-col stats row
      Row(children: [
        _MiniStat(label: 'Nuevos',   value: data.newThisMonth,  color: const Color(0xFF3498DB)),
        const SizedBox(width: 8),
        _MiniStat(label: 'Ganados',  value: data.wonThisMonth,  color: const Color(0xFF27AE60)),
        const SizedBox(width: 8),
        _MiniStat(label: 'Perdidos', value: data.lostThisMonth, color: const Color(0xFFE74C3C)),
      ]),
    ]);
  }
}

class _MiniStat extends StatelessWidget {
  final String label;
  final int value;
  final Color color;
  const _MiniStat({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) => Expanded(
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: color.withOpacity(0.08),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: color.withOpacity(0.3)),
          ),
          child: Column(children: [
            Text('$value', style: TextStyle(color: color, fontSize: 22, fontWeight: FontWeight.bold)),
            const SizedBox(height: 3),
            Text(label, style: const TextStyle(color: _kSubtext, fontSize: 11)),
          ]),
        ),
      );
}

// ─── Pipeline funnel ──────────────────────────────────────────────────────────

class _PipelineFunnelSection extends StatelessWidget {
  final CrmAnalyticsModel data;
  const _PipelineFunnelSection({required this.data});

  static const _order = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];

  @override
  Widget build(BuildContext context) {
    final total = data.pipeline.values.fold(0, (a, b) => a + b);
    final max   = data.pipeline.values.fold(0, (a, b) => b > a ? b : a);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white10),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          const Text('PIPELINE', style: TextStyle(color: _kSubtext, fontSize: 10, letterSpacing: 1.2, fontWeight: FontWeight.w600)),
          const Spacer(),
          Text('Total: ${data.total}', style: const TextStyle(color: _kSubtext, fontSize: 12)),
        ]),
        const SizedBox(height: 14),
        LayoutBuilder(builder: (_, constraints) {
          final availableWidth = constraints.maxWidth - 100; // label+count space
          return Column(
            children: _order.map((status) {
              final count = data.pipeline[status] ?? 0;
              final color = _statusColors[status] ?? _kSubtext;
              final label = _statusLabels[status] ?? status;
              final barW  = max > 0 ? (count / max) * availableWidth : 0.0;
              final pct   = total > 0 ? ((count / total) * 100).toStringAsFixed(0) : '0';

              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(children: [
                  SizedBox(
                    width: 82,
                    child: Text(label,
                        style: const TextStyle(color: _kSubtext, fontSize: 11),
                        overflow: TextOverflow.ellipsis),
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Stack(
                      children: [
                        Container(
                          height: 20,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: color.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 500),
                          height: 20,
                          width: barW > 0 ? barW.clamp(4.0, availableWidth) : 0,
                          decoration: BoxDecoration(
                            color: color.withOpacity(0.7),
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                        Positioned.fill(
                          child: Align(
                            alignment: Alignment.centerLeft,
                            child: Padding(
                              padding: const EdgeInsets.only(left: 6),
                              child: Text(
                                count > 0 ? '$count ($pct%)' : '0',
                                style: TextStyle(
                                  color: count > 0 ? Colors.white.withOpacity(0.9) : _kSubtext,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ]),
              );
            }).toList(),
          );
        }),
      ]),
    );
  }
}

// ─── Activities today ─────────────────────────────────────────────────────────

class _ActivitiesTodaySection extends StatelessWidget {
  final CrmAnalyticsModel data;
  final bool isCurrentMonth;
  const _ActivitiesTodaySection({required this.data, this.isCurrentMonth = true});

  @override
  Widget build(BuildContext context) {
    final sorted = data.activitiesToday.entries.toList()
      ..sort((a, b) => b.value.compareTo(a.value));

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white10),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(isCurrentMonth ? 'ACTIVIDAD HOY' : 'ACTIVIDADES DEL MES',
            style: const TextStyle(color: _kSubtext, fontSize: 10, letterSpacing: 1.2, fontWeight: FontWeight.w600)),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: sorted.map((e) {
            final icon  = _activityIcons[e.key]  ?? Icons.radio_button_unchecked_rounded;
            final label = _activityLabels[e.key] ?? e.key;
            return Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: _kGold.withOpacity(0.08),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: _kGold.withOpacity(0.25)),
              ),
              child: Row(mainAxisSize: MainAxisSize.min, children: [
                Icon(icon, size: 13, color: _kGold),
                const SizedBox(width: 5),
                Text('$label (${e.value})',
                    style: const TextStyle(color: _kText, fontSize: 12, fontWeight: FontWeight.w500)),
              ]),
            );
          }).toList(),
        ),
      ]),
    );
  }
}

// ─── Top agents ───────────────────────────────────────────────────────────────

class _TopAgentsSection extends StatelessWidget {
  final CrmAnalyticsModel data;
  const _TopAgentsSection({required this.data});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white10),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const Text('TOP AGENTES — MES', style: TextStyle(color: _kSubtext, fontSize: 10, letterSpacing: 1.2, fontWeight: FontWeight.w600)),
        const SizedBox(height: 12),
        ...data.topAgents.asMap().entries.map((entry) {
          final rank  = entry.key + 1;
          final agent = entry.value;
          final name  = agent['name'] as String? ?? 'Desconocido';
          final won   = (agent['won'] as num? ?? 0).toInt();
          final isTop = rank == 1;

          return Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(children: [
              Container(
                width: 28, height: 28,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isTop ? _kGold.withOpacity(0.2) : Colors.white.withOpacity(0.06),
                ),
                child: Center(
                  child: Text('$rank',
                      style: TextStyle(
                        color: isTop ? _kGold : _kSubtext,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      )),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(name,
                    style: TextStyle(
                      color: isTop ? _kText : _kSubtext,
                      fontSize: 13,
                      fontWeight: isTop ? FontWeight.bold : FontWeight.normal,
                    )),
              ),
              Row(children: [
                const Icon(Icons.emoji_events_rounded, size: 14, color: Color(0xFF27AE60)),
                const SizedBox(width: 4),
                Text('$won ganado${won != 1 ? 's' : ''}',
                    style: const TextStyle(color: Color(0xFF27AE60), fontSize: 12, fontWeight: FontWeight.bold)),
              ]),
            ]),
          );
        }),
      ]),
    );
  }
}
