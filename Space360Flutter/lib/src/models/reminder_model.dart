class ReminderModel {
  final int id;
  final String title;
  final String? description;
  final String priority;
  final String priorityLabel;
  final String remindAt;
  final bool isOverdue;
  final int? leadId;
  final String? leadName;

  const ReminderModel({
    required this.id,
    required this.title,
    required this.priority,
    required this.priorityLabel,
    required this.remindAt,
    required this.isOverdue,
    this.description,
    this.leadId,
    this.leadName,
  });

  factory ReminderModel.fromJson(Map<String, dynamic> j) => ReminderModel(
        id:            j['id'] as int,
        title:         j['title'] as String? ?? '',
        description:   j['description'] as String?,
        priority:      j['priority'] as String? ?? 'medium',
        priorityLabel: j['priority_label'] as String? ?? '',
        remindAt:      j['remind_at'] as String? ?? '',
        isOverdue:     j['is_overdue'] as bool? ?? false,
        leadId:        j['lead_id'] as int?,
        leadName:      j['lead_name'] as String?,
      );

  bool get isDueToday {
    try {
      final dt  = DateTime.parse(remindAt).toLocal();
      final now = DateTime.now();
      return dt.year == now.year && dt.month == now.month && dt.day == now.day;
    } catch (_) {
      return false;
    }
  }
}
