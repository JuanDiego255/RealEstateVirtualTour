class CrmAgent {
  final int id;
  final String name;
  const CrmAgent({required this.id, required this.name});
  factory CrmAgent.fromJson(Map<String, dynamic> j) =>
      CrmAgent(id: j['id'] as int, name: j['name'] as String? ?? '');
}

class CrmVehicle {
  final int id;
  final String name;
  final String? image;
  final String? price;
  const CrmVehicle({required this.id, required this.name, this.image, this.price});
  factory CrmVehicle.fromJson(Map<String, dynamic> j) => CrmVehicle(
        id:    j['id'] as int,
        name:  j['name'] as String? ?? '',
        image: j['image'] as String?,
        price: j['price'] as String?,
      );
}

class CrmLeadActivity {
  final int id;
  final String type;
  final String typeLabel;
  final String? subject;
  final String? description;
  final String? callResult;
  final String? oldStatus;
  final String? newStatus;
  final String activityAt;
  final CrmAgent? agent;

  const CrmLeadActivity({
    required this.id,
    required this.type,
    required this.typeLabel,
    required this.activityAt,
    this.subject,
    this.description,
    this.callResult,
    this.oldStatus,
    this.newStatus,
    this.agent,
  });

  factory CrmLeadActivity.fromJson(Map<String, dynamic> j) => CrmLeadActivity(
        id:          j['id'] as int,
        type:        j['type'] as String? ?? '',
        typeLabel:   j['type_label'] as String? ?? j['type'] as String? ?? '',
        subject:     j['subject'] as String?,
        description: j['description'] as String?,
        callResult:  j['call_result'] as String?,
        oldStatus:   j['old_status'] as String?,
        newStatus:   j['new_status'] as String?,
        activityAt:  j['activity_at'] as String? ?? '',
        agent:       j['agent'] != null ? CrmAgent.fromJson(j['agent'] as Map<String, dynamic>) : null,
      );
}

class CrmLead {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? whatsapp;
  final String status;
  final String statusLabel;
  final String priority;
  final String source;
  final String sourceLabel;
  final String interestType;
  final double? budgetMin;
  final double? budgetMax;
  final String budgetCurrency;
  final String? notes;
  final String? requirements;
  final String? nextFollowUp;
  final String? firstContactAt;
  final String? lastContactAt;
  final String? eventName;
  final int? eventLeadId;
  final String createdAt;
  final CrmAgent? agent;
  final CrmVehicle? vehicle;
  final List<CrmLeadActivity> activities;

  const CrmLead({
    required this.id,
    required this.name,
    required this.phone,
    required this.status,
    required this.statusLabel,
    required this.priority,
    required this.source,
    required this.sourceLabel,
    required this.interestType,
    required this.budgetCurrency,
    required this.createdAt,
    this.email,
    this.whatsapp,
    this.budgetMin,
    this.budgetMax,
    this.notes,
    this.requirements,
    this.nextFollowUp,
    this.firstContactAt,
    this.lastContactAt,
    this.eventName,
    this.eventLeadId,
    this.agent,
    this.vehicle,
    this.activities = const [],
  });

  factory CrmLead.fromJson(Map<String, dynamic> j) => CrmLead(
        id:            j['id'] as int,
        name:          j['name'] as String? ?? '',
        phone:         j['phone'] as String? ?? '',
        email:         j['email'] as String?,
        whatsapp:      j['whatsapp'] as String?,
        status:        j['status'] as String? ?? 'new',
        statusLabel:   j['status_label'] as String? ?? j['status'] as String? ?? '',
        priority:      j['priority'] as String? ?? 'medium',
        source:        j['source'] as String? ?? '',
        sourceLabel:   j['source_label'] as String? ?? j['source'] as String? ?? '',
        interestType:  j['interest_type'] as String? ?? 'buy',
        budgetMin:     (j['budget_min'] as num?)?.toDouble(),
        budgetMax:     (j['budget_max'] as num?)?.toDouble(),
        budgetCurrency: j['budget_currency'] as String? ?? 'CRC',
        notes:         j['notes'] as String?,
        requirements:  j['requirements'] as String?,
        nextFollowUp:  j['next_follow_up'] as String?,
        firstContactAt: j['first_contact_at'] as String?,
        lastContactAt:  j['last_contact_at'] as String?,
        eventName:     j['event_name'] as String?,
        eventLeadId:   j['event_lead_id'] as int?,
        createdAt:     j['created_at'] as String? ?? '',
        agent:         j['agent'] != null ? CrmAgent.fromJson(j['agent'] as Map<String, dynamic>) : null,
        vehicle:       j['vehicle'] != null ? CrmVehicle.fromJson(j['vehicle'] as Map<String, dynamic>) : null,
        activities:    (j['activities'] as List<dynamic>?)
            ?.map((e) => CrmLeadActivity.fromJson(e as Map<String, dynamic>))
            .toList() ?? const [],
      );
}

class CrmStats {
  final int total;
  final int newLeads;
  final int active;
  final int won;
  final int fromEvents;
  final int needsFollowUp;

  const CrmStats({
    required this.total,
    required this.newLeads,
    required this.active,
    required this.won,
    required this.fromEvents,
    required this.needsFollowUp,
  });

  factory CrmStats.fromJson(Map<String, dynamic> j) => CrmStats(
        total:         (j['total'] as num?)?.toInt() ?? 0,
        newLeads:      (j['new'] as num?)?.toInt() ?? 0,
        active:        (j['active'] as num?)?.toInt() ?? 0,
        won:           (j['won'] as num?)?.toInt() ?? 0,
        fromEvents:    (j['from_events'] as num?)?.toInt() ?? 0,
        needsFollowUp: (j['needs_follow_up'] as num?)?.toInt() ?? 0,
      );
}

class CrmLeadPage {
  final List<CrmLead> leads;
  final int total;
  final int currentPage;
  final int lastPage;
  final CrmStats stats;

  const CrmLeadPage({
    required this.leads,
    required this.total,
    required this.currentPage,
    required this.lastPage,
    required this.stats,
  });

  factory CrmLeadPage.fromJson(Map<String, dynamic> j) => CrmLeadPage(
        leads:       (j['leads'] as List<dynamic>)
            .map((e) => CrmLead.fromJson(e as Map<String, dynamic>))
            .toList(),
        total:       (j['total'] as num?)?.toInt() ?? 0,
        currentPage: (j['current_page'] as num?)?.toInt() ?? 1,
        lastPage:    (j['last_page'] as num?)?.toInt() ?? 1,
        stats:       CrmStats.fromJson(j['stats'] as Map<String, dynamic>? ?? {}),
      );
}

// ─── Vehicle Quote for Cotizaciones tab ──────────────────────────────────────

class VehicleQuoteModel {
  final int id;
  final String? customerName;
  final String? customerPhone;
  final String? customerEmail;
  final CrmVehicle? vehicle;
  final double vehiclePrice;
  final double downPayment;
  final double downPaymentPercent;
  final int? termMonths;
  final double? interestRate;
  final double monthlyPayment;
  final double totalAmount;
  final String currency;
  final String? eventName;
  final CrmAgent? agent;
  final String createdAt;

  const VehicleQuoteModel({
    required this.id,
    required this.vehiclePrice,
    required this.downPayment,
    required this.downPaymentPercent,
    required this.monthlyPayment,
    required this.totalAmount,
    required this.currency,
    required this.createdAt,
    this.customerName,
    this.customerPhone,
    this.customerEmail,
    this.vehicle,
    this.termMonths,
    this.interestRate,
    this.eventName,
    this.agent,
  });

  String get customerDisplay => customerName ?? customerPhone ?? 'Sin nombre';

  String _fmt(double v) {
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

  String get monthlyFormatted => (currency == 'USD' ? '\$' : '₡') + _fmt(monthlyPayment);
  String get vehiclePriceFormatted => (currency == 'USD' ? '\$' : '₡') + _fmt(vehiclePrice);

  factory VehicleQuoteModel.fromJson(Map<String, dynamic> j) => VehicleQuoteModel(
        id:                   j['id'] as int,
        customerName:         j['customer_name'] as String?,
        customerPhone:        j['customer_phone'] as String?,
        customerEmail:        j['customer_email'] as String?,
        vehiclePrice:         (j['vehicle_price'] as num?)?.toDouble() ?? 0,
        downPayment:          (j['down_payment'] as num?)?.toDouble() ?? 0,
        downPaymentPercent:   (j['down_payment_percent'] as num?)?.toDouble() ?? 0,
        termMonths:           (j['term_months'] as num?)?.toInt(),
        interestRate:         (j['interest_rate'] as num?)?.toDouble(),
        monthlyPayment:       (j['monthly_payment'] as num?)?.toDouble() ?? 0,
        totalAmount:          (j['total_amount'] as num?)?.toDouble() ?? 0,
        currency:             j['currency'] as String? ?? 'CRC',
        eventName:            j['event_name'] as String?,
        vehicle:              j['vehicle'] != null ? CrmVehicle.fromJson(j['vehicle'] as Map<String, dynamic>) : null,
        agent:                j['agent'] != null ? CrmAgent.fromJson(j['agent'] as Map<String, dynamic>) : null,
        createdAt:            j['created_at'] as String? ?? '',
      );
}
