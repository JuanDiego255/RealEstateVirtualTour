class LeadFollowup {
  final int id;
  final String action;
  final String outcome;
  final String? notes;
  final String? nextFollowupAt;
  final String createdAt;
  final EventLeadAgent? agent;

  const LeadFollowup({
    required this.id,
    required this.action,
    required this.outcome,
    required this.createdAt,
    this.notes,
    this.nextFollowupAt,
    this.agent,
  });

  factory LeadFollowup.fromJson(Map<String, dynamic> j) => LeadFollowup(
        id:              j['id'] as int,
        action:          j['action'] as String? ?? '',
        outcome:         j['outcome'] as String? ?? '',
        notes:           j['notes'] as String?,
        nextFollowupAt:  j['next_followup_at'] as String?,
        createdAt:       j['created_at'] as String? ?? '',
        agent:           j['agent'] != null
            ? EventLeadAgent.fromJson(j['agent'] as Map<String, dynamic>)
            : null,
      );
}

class EventLeadVehicle {
  final int id;
  final String name;
  final String? image;

  const EventLeadVehicle({required this.id, required this.name, this.image});

  factory EventLeadVehicle.fromJson(Map<String, dynamic> j) => EventLeadVehicle(
        id: j['id'] as int,
        name: j['name'] as String? ?? '',
        image: j['image'] as String?,
      );
}

class EventLeadAgent {
  final int id;
  final String name;

  const EventLeadAgent({required this.id, required this.name});

  factory EventLeadAgent.fromJson(Map<String, dynamic> j) => EventLeadAgent(
        id: j['id'] as int,
        name: j['name'] as String? ?? '',
      );
}

class EventLeadModel {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String interestLevel;
  final String? leadCategory;
  final String source;
  final bool contacted;
  final String? contactedAt;
  final String? contactedBy;
  final String? notes;
  final String createdAt;
  final EventLeadAgent? agent;
  final EventLeadVehicle? vehicle;
  final List<LeadFollowup> followups;

  const EventLeadModel({
    required this.id,
    required this.name,
    required this.phone,
    required this.interestLevel,
    required this.source,
    required this.contacted,
    required this.createdAt,
    this.email,
    this.leadCategory,
    this.contactedAt,
    this.contactedBy,
    this.notes,
    this.agent,
    this.vehicle,
    this.followups = const [],
  });

  factory EventLeadModel.fromJson(Map<String, dynamic> j) => EventLeadModel(
        id:             j['id'] as int,
        name:           j['name'] as String? ?? '',
        phone:          j['phone'] as String? ?? '',
        email:          j['email'] as String?,
        interestLevel:  j['interest_level'] as String? ?? 'medium',
        leadCategory:   j['lead_category'] as String?,
        source:         j['source'] as String? ?? 'kiosk',
        contacted:      j['contacted'] == true,
        contactedAt:    j['contacted_at'] as String?,
        contactedBy:    j['contacted_by'] as String?,
        notes:          j['notes'] as String?,
        createdAt:      j['created_at'] as String? ?? '',
        agent:          j['agent'] != null ? EventLeadAgent.fromJson(j['agent'] as Map<String, dynamic>) : null,
        vehicle:        j['vehicle'] != null ? EventLeadVehicle.fromJson(j['vehicle'] as Map<String, dynamic>) : null,
        followups:      (j['followups'] as List<dynamic>?)
            ?.map((e) => LeadFollowup.fromJson(e as Map<String, dynamic>))
            .toList() ?? const [],
      );
}

class DashboardStats {
  final int totalLeads;
  final int leadsToday;
  final int leadsHot;
  final int leadsPending;
  final int quotesToday;

  const DashboardStats({
    required this.totalLeads,
    required this.leadsToday,
    required this.leadsHot,
    required this.leadsPending,
    required this.quotesToday,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> j) => DashboardStats(
        totalLeads:   (j['total_leads'] as num?)?.toInt() ?? 0,
        leadsToday:   (j['leads_today'] as num?)?.toInt() ?? 0,
        leadsHot:     (j['leads_hot'] as num?)?.toInt() ?? 0,
        leadsPending: (j['leads_pending'] as num?)?.toInt() ?? 0,
        quotesToday:  (j['quotes_today'] as num?)?.toInt() ?? 0,
      );
}
