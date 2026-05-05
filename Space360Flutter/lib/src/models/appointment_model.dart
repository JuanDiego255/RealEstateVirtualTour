class AppointmentModel {
  final int id;
  final String title;
  final String type;
  final String typeLabel;
  final String status;
  final String statusLabel;
  final String startsAt;
  final String? endsAt;
  final String? location;
  final String? description;
  final String? outcome;
  final String? outcomeLabel;
  final String? outcomeNotes;
  final AppointmentLead? lead;
  final AppointmentAgent? agent;

  const AppointmentModel({
    required this.id,
    required this.title,
    required this.type,
    required this.typeLabel,
    required this.status,
    required this.statusLabel,
    required this.startsAt,
    this.endsAt,
    this.location,
    this.description,
    this.outcome,
    this.outcomeLabel,
    this.outcomeNotes,
    this.lead,
    this.agent,
  });

  factory AppointmentModel.fromJson(Map<String, dynamic> j) => AppointmentModel(
        id:           j['id'] as int,
        title:        j['title'] as String? ?? '',
        type:         j['type'] as String? ?? 'other',
        typeLabel:    j['type_label'] as String? ?? j['type'] as String? ?? '',
        status:       j['status'] as String? ?? 'scheduled',
        statusLabel:  j['status_label'] as String? ?? j['status'] as String? ?? '',
        startsAt:     j['starts_at'] as String? ?? '',
        endsAt:       j['ends_at'] as String?,
        location:     j['location'] as String?,
        description:  j['description'] as String?,
        outcome:      j['outcome'] as String?,
        outcomeLabel: j['outcome_label'] as String?,
        outcomeNotes: j['outcome_notes'] as String?,
        lead:         j['lead'] != null ? AppointmentLead.fromJson(j['lead'] as Map<String, dynamic>) : null,
        agent:        j['agent'] != null ? AppointmentAgent.fromJson(j['agent'] as Map<String, dynamic>) : null,
      );

  bool get isPast {
    try {
      return DateTime.parse(startsAt).isBefore(DateTime.now());
    } catch (_) {
      return false;
    }
  }

  bool get isToday {
    try {
      final dt = DateTime.parse(startsAt).toLocal();
      final now = DateTime.now();
      return dt.year == now.year && dt.month == now.month && dt.day == now.day;
    } catch (_) {
      return false;
    }
  }
}

class AppointmentLead {
  final int id;
  final String name;
  final String? phone;
  const AppointmentLead({required this.id, required this.name, this.phone});
  factory AppointmentLead.fromJson(Map<String, dynamic> j) => AppointmentLead(
        id:    j['id'] as int,
        name:  j['name'] as String? ?? '',
        phone: j['phone'] as String?,
      );
}

class AppointmentAgent {
  final int id;
  final String name;
  const AppointmentAgent({required this.id, required this.name});
  factory AppointmentAgent.fromJson(Map<String, dynamic> j) =>
      AppointmentAgent(id: j['id'] as int, name: j['name'] as String? ?? '');
}
