import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:space360_flutter/src/models/appointment_model.dart';
import 'package:space360_flutter/src/models/crm_lead_model.dart';
import 'package:space360_flutter/src/models/reminder_model.dart';
import 'package:space360_flutter/src/services/api_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kHost = 'space360cr.com';

// ─── Analytics model ──────────────────────────────────────────────────────────

class CrmAnalyticsModel {
  final Map<String, int> pipeline;
  final int total;
  final int wonThisMonth;
  final int lostThisMonth;
  final int newThisMonth;
  final double conversionRate;
  final Map<String, int> activitiesToday;
  final List<Map<String, dynamic>> topAgents;

  const CrmAnalyticsModel({
    required this.pipeline,
    required this.total,
    required this.wonThisMonth,
    required this.lostThisMonth,
    required this.newThisMonth,
    required this.conversionRate,
    required this.activitiesToday,
    required this.topAgents,
  });

  factory CrmAnalyticsModel.fromJson(Map<String, dynamic> j) {
    final pipelineRaw  = j['pipeline'] as Map<String, dynamic>? ?? {};
    final activitiesRaw = j['activities_today'] as Map<String, dynamic>? ?? {};
    final agentsRaw    = j['top_agents'] as List<dynamic>? ?? [];
    return CrmAnalyticsModel(
      pipeline: pipelineRaw.map((k, v) => MapEntry(k, (v as num).toInt())),
      total:           (j['total']           as num? ?? 0).toInt(),
      wonThisMonth:    (j['won_this_month']   as num? ?? 0).toInt(),
      lostThisMonth:   (j['lost_this_month']  as num? ?? 0).toInt(),
      newThisMonth:    (j['new_this_month']   as num? ?? 0).toInt(),
      conversionRate:  (j['conversion_rate']  as num? ?? 0).toDouble(),
      activitiesToday: activitiesRaw.map((k, v) => MapEntry(k, (v as num).toInt())),
      topAgents:       agentsRaw.cast<Map<String, dynamic>>(),
    );
  }
}

class CrmAgentModel {
  final int id;
  final String name;
  final String? email;
  final String role;
  const CrmAgentModel({required this.id, required this.name, this.email, required this.role});

  factory CrmAgentModel.fromJson(Map<String, dynamic> j) => CrmAgentModel(
    id:    (j['id'] as num).toInt(),
    name:  j['name'] as String,
    email: j['email'] as String?,
    role:  j['role'] as String,
  );
}

class CrmService {
  final _api = ApiService();

  // ─── Leads ────────────────────────────────────────────────────────────────

  Future<Resource<CrmLeadPage>> getLeads({
    String? status,
    String? priority,
    String? origin, // all | event | agency
    String? search,
    int? agentId,
    int page = 1,
    int perPage = 25,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final params  = <String, String>{'page': '$page', 'per_page': '$perPage'};
      if (status   != null && status   != 'all') params['status']   = status;
      if (priority  != null && priority  != 'all') params['priority']  = priority;
      if (origin    != null && origin    != 'all') params['origin']    = origin;
      if (search    != null && search.isNotEmpty)  params['search']    = search;
      if (agentId   != null)                       params['agent_id']  = '$agentId';

      final res = await http.get(
        Uri.https(_kHost, '/api/crm/leads', params),
        headers: headers,
      );
      if (res.statusCode == 200) {
        return Success(CrmLeadPage.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      if (res.statusCode == 403) return AppError('Sin permiso para acceder al CRM.');
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<CrmLead>> getLead(int id) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/crm/leads/$id'), headers: headers);
      if (res.statusCode == 200) {
        return Success(CrmLead.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<int>> createLead({
    required String name,
    required String phone,
    String? email,
    String? whatsapp,
    String source = 'other',
    String priority = 'medium',
    String interestType = 'buy',
    String? notes,
    int? vehicleId,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/crm/leads'),
        headers: headers,
        body: jsonEncode({
          'name':          name,
          'phone':         phone,
          if (email     != null) 'email':         email,
          if (whatsapp  != null) 'whatsapp':      whatsapp,
          'source':        source,
          'priority':      priority,
          'interest_type': interestType,
          if (notes     != null) 'notes':         notes,
          if (vehicleId != null) 'vehicle_id':    vehicleId,
        }),
      );
      if (res.statusCode == 201) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        return Success((data['lead_id'] as num).toInt());
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al crear lead');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> changeStatus(int leadId, String status, {String? note}) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.patch(
        Uri.https(_kHost, '/api/crm/leads/$leadId/status'),
        headers: headers,
        body: jsonEncode({'status': status, if (note != null) 'note': note}),
      );
      if (res.statusCode == 200) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> logActivity({
    required int leadId,
    required String type,
    required String subject,
    String? description,
    String? callResult,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/crm/leads/$leadId/activity'),
        headers: headers,
        body: jsonEncode({
          'type':    type,
          'subject': subject,
          if (description != null) 'description': description,
          if (callResult  != null) 'call_result':  callResult,
        }),
      );
      if (res.statusCode == 200) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Event integration ────────────────────────────────────────────────────

  /// Returns Success(leadId) on create, Duplicate(msg, leadId) on conflict, AppError otherwise.
  Future<Resource<int>> addEventLeadToCrm(int eventLeadId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/event-leads/$eventLeadId/add-to-crm'),
        headers: headers,
      );
      if (res.statusCode == 201) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        return Success((data['lead_id'] as num).toInt());
      }
      if (res.statusCode == 409) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final leadId = (data['lead_id'] as num?)?.toInt() ?? 0;
        return Duplicate(data['message']?.toString() ?? 'Ya existe en CRM', leadId);
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<int>> addQuoteToCrm(int quoteId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/quotes/$quoteId/add-to-crm'),
        headers: headers,
      );
      if (res.statusCode == 201) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        return Success((data['lead_id'] as num).toInt());
      }
      if (res.statusCode == 409) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final leadId = (data['lead_id'] as num?)?.toInt() ?? 0;
        return Duplicate(data['message']?.toString() ?? 'Ya existe en CRM', leadId);
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Quotes (Cotizaciones tab) ────────────────────────────────────────────

  Future<Resource<List<VehicleQuoteModel>>> getQuotes({int page = 1}) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(
        Uri.https(_kHost, '/api/kiosk/quotes', {'page': '$page'}),
        headers: headers,
      );
      if (res.statusCode == 200) {
        final data  = jsonDecode(res.body) as Map<String, dynamic>;
        final list  = (data['quotes'] as List<dynamic>)
            .map((e) => VehicleQuoteModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Update lead ──────────────────────────────────────────────────────────

  Future<Resource<bool>> updateLead(int id, Map<String, dynamic> fields) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.patch(
        Uri.https(_kHost, '/api/crm/leads/$id'),
        headers: headers,
        body: jsonEncode(fields),
      );
      if (res.statusCode == 200) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al actualizar');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Appointments ─────────────────────────────────────────────────────────

  Future<Resource<List<AppointmentModel>>> getLeadAppointments(int leadId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(
        Uri.https(_kHost, '/api/crm/leads/$leadId/appointments'),
        headers: headers,
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['appointments'] as List<dynamic>)
            .map((e) => AppointmentModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<int>> createAppointment({
    required int leadId,
    required String title,
    required String type,
    required String startsAt,
    String? endsAt,
    String? location,
    String? description,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/crm/appointments'),
        headers: headers,
        body: jsonEncode({
          'lead_id':    leadId,
          'title':      title,
          'type':       type,
          'starts_at':  startsAt,
          if (endsAt      != null) 'ends_at':     endsAt,
          if (location    != null) 'location':    location,
          if (description != null) 'description': description,
        }),
      );
      if (res.statusCode == 201) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        return Success((data['appointment_id'] as num).toInt());
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al crear cita');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> updateAppointmentStatus(
    int appointmentId,
    String status, {
    String? outcome,
    String? outcomeNotes,
    String? cancellationReason,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.patch(
        Uri.https(_kHost, '/api/crm/appointments/$appointmentId/status'),
        headers: headers,
        body: jsonEncode({
          'status': status,
          if (outcome            != null) 'outcome':              outcome,
          if (outcomeNotes       != null) 'outcome_notes':        outcomeNotes,
          if (cancellationReason != null) 'cancellation_reason':  cancellationReason,
        }),
      );
      if (res.statusCode == 200) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<List<AppointmentModel>>> getAgenda({int days = 14}) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(
        Uri.https(_kHost, '/api/crm/agenda', {'days': '$days'}),
        headers: headers,
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['appointments'] as List<dynamic>)
            .map((e) => AppointmentModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Reminders ────────────────────────────────────────────────────────────

  Future<Resource<List<ReminderModel>>> getReminders() async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/crm/reminders'), headers: headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['reminders'] as List<dynamic>)
            .map((e) => ReminderModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<List<ReminderModel>>> getLeadReminders(int leadId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(
        Uri.https(_kHost, '/api/crm/leads/$leadId/reminders'),
        headers: headers,
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['reminders'] as List<dynamic>)
            .map((e) => ReminderModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<int>> createReminder({
    required int leadId,
    required String title,
    required String remindAt,
    String priority = 'medium',
    String? description,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/crm/leads/$leadId/reminders'),
        headers: headers,
        body: jsonEncode({
          'title':       title,
          'remind_at':   remindAt,
          'priority':    priority,
          if (description != null) 'description': description,
        }),
      );
      if (res.statusCode == 201) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        return Success((data['reminder_id'] as num).toInt());
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al crear recordatorio');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> completeReminder(int reminderId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.patch(
        Uri.https(_kHost, '/api/crm/reminders/$reminderId/complete'),
        headers: headers,
      );
      if (res.statusCode == 200) return Success(true);
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> snoozeReminder(int reminderId, {int minutes = 15}) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.patch(
        Uri.https(_kHost, '/api/crm/reminders/$reminderId/snooze'),
        headers: headers,
        body: jsonEncode({'minutes': minutes}),
      );
      if (res.statusCode == 200) return Success(true);
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> deleteReminder(int reminderId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.delete(
        Uri.https(_kHost, '/api/crm/reminders/$reminderId'),
        headers: headers,
      );
      if (res.statusCode == 200) return Success(true);
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> updateAppointment(int id, Map<String, dynamic> fields) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.patch(
        Uri.https(_kHost, '/api/crm/appointments/$id'),
        headers: headers,
        body: jsonEncode(fields),
      );
      if (res.statusCode == 200) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al actualizar');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<List<CrmAgentModel>>> getAgents() async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/crm/agents'), headers: headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['agents'] as List<dynamic>)
            .map((e) => CrmAgentModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Analytics ────────────────────────────────────────────────────────────

  Future<Resource<CrmAnalyticsModel>> getAnalytics({int? month, int? year}) async {
    try {
      final headers = await _api.authHeaders();
      final params  = <String, String>{};
      if (month != null) params['month'] = '$month';
      if (year  != null) params['year']  = '$year';
      final res = await http.get(
        Uri.https(_kHost, '/api/crm/analytics', params.isEmpty ? null : params),
        headers: headers,
      );
      if (res.statusCode == 200) {
        return Success(CrmAnalyticsModel.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }
}
